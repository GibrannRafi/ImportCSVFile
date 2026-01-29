<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\{Article, Category, Publisher, Reporter, ArticleMeta, Tags, User};

class ImportArticlesCommand extends Command
{
    protected $signature = 'import:articles {path}';
    protected $description = 'Import and normalize articles from CSV with polymorphic metadata';

    public function handle(): void
    {
        $path = $this->argument('path');

        if (!$this->validateFile($path)) return;

        $file = fopen($path, 'r');
        fgetcsv($file); // Skip header

        $this->info("🚀 Memulai proses import...");
        $rowNumber = 1;

        while (($lineArray = fgetcsv($file)) !== false) {
            $rowNumber++;
            $this->processRow($lineArray, $rowNumber);
        }

        fclose($file);
        $this->info("\n✅ Import selesai! Data tersimpan dengan aman.");
    }

    /**
     * Memproses setiap baris CSV dalam satu transaksi database.
     */
    private function processRow(array $lineArray, int $rowNumber): void
    {
        $fullLine = implode(' ', $lineArray);

        DB::beginTransaction();
        try {
            $publisher = Publisher::firstOrCreate(['name' => $lineArray[0] ?? 'Default Publisher']);

            // Extract & Process Entities
            $user     = $this->resolveUser($fullLine, $publisher->id, $rowNumber);
            $category = $this->resolveCategory($fullLine, $publisher->id);
            $reporter = $this->resolveReporter($fullLine, $publisher->id);

            // Content Cleaning
            $cleanContent = $this->cleanArticleContent($lineArray[3] ?? '', $lineArray);

            // Persistence
            $article = $this->persistArticle($lineArray, $cleanContent, $publisher->id, $user->id, $category->id);

            // Relationships
            $this->attachMetadata($article, $reporter, $lineArray[4] ?? '[]', $publisher->id);

            DB::commit();
            $this->output->write(".");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Gagal di baris $rowNumber: " . $e->getMessage());
        }
    }

    /**
     * Membersihkan konten HTML dan mengintegrasikan URL Foto.
     */
    private function cleanArticleContent(string $rawContent, array $row): string
    {
        $photoUrl = $this->extractPhotoUrl($row);

        // Replace placeholder, hapus tag HTML, dan bersihkan entities
        $content = str_replace('', $photoUrl, $rawContent);
        $content = strip_tags($content);
        $content = html_entity_decode($content);
        $content = str_replace("\xc2\xa0", ' ', $content);

        return trim(preg_replace('/\s+/', ' ', $content));
    }

    /**
     * Mencari URL foto dari kolom index 5 sampai 9.
     */
    private function extractPhotoUrl(array $row): string
    {
        for ($i = 5; $i <= 9; $i++) {
            if (!empty($row[$i])) {
                $decoded = json_decode($row[$i], true);
                if (is_array($decoded) && isset($decoded[0]['src'])) {
                    return $decoded[0]['src'];
                }
            }
        }
        return '';
    }

    /**
     * Menyimpan atau memperbarui data artikel tanpa merusak integritas ID (Foreign Key).
     */
    private function persistArticle(array $row, string $content, int $pubId, string $userId, string $catId): Article
    {
        $title = $row[1] ?? 'Untitled';
        $slug  = Str::slug($title);

        $article = Article::where('slug', $slug)->where('publisher_id', $pubId)->first();

        $data = [
            'user_id'      => $userId,
            'category_id'  => $catId,
            'title'        => $title,
            'description'  => substr($row[2] ?? '', 0, 255),
            'content'      => $content,
            'published_at' => now(),
        ];

        if (!$article) {
            $data['id']           = (string) Str::uuid();
            $data['article_id']   = $this->generateUniqueArticleId();
            $data['publisher_id'] = $pubId;
            $data['slug']         = $slug;
            $data['status']       = 'published';

            return Article::create($data);
        }

        $article->update($data);
        return $article;
    }

    /**
     * Mengelola metadata polymorphic (Reporter & Tags).
     */
    private function attachMetadata(Article $article, Reporter $reporter, string $tagsJson, int $pubId): void
    {
        // Reporter Meta
        ArticleMeta::firstOrCreate([
            'article_id' => $article->id,
            'meta_id'    => $reporter->id,
            'meta_type'  => Reporter::class,
        ]);

        // Tags Meta
        $tags = json_decode($tagsJson, true);
        if (is_array($tags)) {
            foreach ($tags as $item) {
                if (empty($item['name']) || isset($item['src'])) continue;

                $tag = Tags::firstOrCreate(
                    ['name' => $item['name'], 'publisher_id' => $pubId],
                    ['id' => (string) Str::uuid(), 'slug' => Str::slug($item['name'])]
                );

                DB::table('article_meta')->updateOrInsert([
                    'article_id' => $article->id,
                    'meta_id'    => $tag->id,
                    'meta_type'  => Tags::class,
                ]);
            }
        }
    }

    // --- Resolver Helpers ---

    private function resolveUser(string $text, int $pubId, int $row): User
    {
        $data = $this->findJsonByKeys($text, ['email'], false);
        return User::firstOrCreate(
            ['email' => $data['email'] ?? "system_$row@cms.local"],
            ['id' => (string) Str::uuid(), 'name' => $data['name'] ?? 'Editor', 'publisher_id' => $pubId]
        );
    }

    private function resolveCategory(string $text, int $pubId): Category
    {
        $data = $this->findJsonByKeys($text, ['name', 'alias'], false);
        $name = $data['name'] ?? 'Uncategorized';
        return Category::firstOrCreate(
            ['name' => $name, 'publisher_id' => $pubId],
            ['id' => (string) Str::uuid(), 'slug' => Str::slug($name), 'parent_id' => $data['parent'] ?? null]
        );
    }

    private function resolveReporter(string $text, int $pubId): Reporter
    {
        $data = $this->findJsonByKeys($text, ['name', 'photo'], true);
        $name = $data['name'] ?? 'Admin';
        return Reporter::firstOrCreate(
            ['name' => $name, 'publisher_id' => $pubId],
            ['id' => (string) Str::uuid(), 'slug' => Str::slug($name)]
        );
    }

    // --- Utilities ---

    private function generateUniqueArticleId(): string
    {
        do {
            $id = Str::random(10);
        } while (Article::where('article_id', $id)->exists());
        return $id;
    }

    private function validateFile(string $path): bool
    {
        if (!file_exists($path)) {
            $this->error("File tidak ditemukan di: $path");
            return false;
        }
        return true;
    }

    private function findJsonByKeys(string $text, array $keys, bool $shouldBeInArray): ?array
    {
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $text, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            $offset = $match[1];
            if ($shouldBeInArray !== ($offset > 0 && $text[$offset - 1] === '[')) continue;

            $data = json_decode($match[0], true);
            if (is_array($data)) {
                foreach ($keys as $key) {
                    if (isset($data[$key])) return $data;
                }
            }
        }
        return null;
    }
}
