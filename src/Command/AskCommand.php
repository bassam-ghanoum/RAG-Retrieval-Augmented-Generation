<?php
declare(strict_types=1);

namespace App\Command;

use App\Rag\EmbeddingClient;
use App\Rag\GeminiClient;
use App\Rag\QdrantClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'app:ask', description: 'Ask a question using RAG.')]
final class AskCommand extends Command
{
    public function __construct(private readonly EmbeddingClient $embeddingClient, private readonly QdrantClient $qdrantClient, private readonly GeminiClient $geminiClient)
    { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('question', InputArgument::REQUIRED);
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Number of context chunks', '3');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $question = trim((string)$input->getArgument('question'));
        $limit = filter_var($input->getOption('limit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
        if ($question === '' || $limit === false) {
            $output->writeln('<error>Provide a non-empty question and a limit between 1 and 10.</error>');
            return Command::FAILURE;
        }
        try {
            $vector = $this->embeddingClient->embed($question);
            $matches = $this->qdrantClient->search($vector, $limit);
            $context = implode("\n\n---\n\n", array_map(
                static fn(array $match): string => (string)($match['payload']['text'] ?? ''),
                $matches
            ));
            $answer = $this->geminiClient->generate($question, $context);
        } catch (\Throwable $exception) {
            $output->writeln('<error>'.trim($exception->getMessage()).'</error>');
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Answer:</info>\n".$answer);
        return Command::SUCCESS;
    }
}
