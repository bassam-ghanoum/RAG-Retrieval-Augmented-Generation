<?php
declare(strict_types=1);

namespace App\Command;

use App\Rag\EmbeddingClient;
use App\Rag\QdrantClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'app:ingest', description: 'Embed rules.md and store the vectors in Qdrant.')]
final class IngestCommand extends Command
{
    public function __construct(private readonly EmbeddingClient $embeddingClient, private readonly QdrantClient $qdrantClient)
    { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = (string)$input->getOption('file');
        $file = str_starts_with($file, '/') ? $file : dirname(__DIR__, 2).'/'.$file;
        $content = is_file($file) ? file_get_contents($file) : false;
        if ($content === false) {
            $output->writeln('<error>Cannot read knowledge file: '.$file.'</error>');
            return Command::FAILURE;
        }
        $chunks = $this->chunkMarkdown($content);
        if ($chunks === []) {
            $output->writeln('<error>The knowledge file is empty.</error>');
            return Command::FAILURE;
        }

        try {
            $this->qdrantClient->recreateCollection();
            foreach ($chunks as $i => $chunk) {
                $output->writeln('Embedding chunk '.($i + 1).' of '.count($chunks).'...');
                $this->qdrantClient->upsert(
                    $i + 1,
                    $this->embeddingClient->embed($chunk),
                    ['text' => $chunk, 'source' => basename($file)]
                );
            }
        } catch (\Throwable $exception) {
            $output->writeln('<error>'.trim($exception->getMessage()).'</error>');
            return Command::FAILURE;
        }
        $output->writeln('<info>Stored '.count($chunks).' chunks in Qdrant.</info>');
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'Knowledge file to ingest', 'knowledge/rules.md');
    }

    private function chunkMarkdown(string $content): array
    {
        $chunks = preg_split('/\n(?=#{1,6}\s)/', trim($content)) ?: [];
        $result = [];
        foreach ($chunks as $chunk) {
            $chunk = trim(preg_replace('/[ \t]+\R/', "\n", $chunk) ?? $chunk);
            if ($chunk !== '') {
                $result[] = $chunk;
            }
        }
        return $result;
    }
}
