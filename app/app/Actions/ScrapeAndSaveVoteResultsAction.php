<?php

namespace App\Actions;

use App\Document;
use App\Enums\DocumentTypeEnum;
use App\Term;
use App\Vote;
use Illuminate\Support\Carbon;

class ScrapeAndSaveVoteResultsAction
{
    private $scrapeAction;

    public function __construct(ScrapeAction $scrapeAction)
    {
        $this->scrapeAction = $scrapeAction;
    }

    public function execute(Term $term, Carbon $date): void
    {
        $response = $this->scrapeAction->execute('vote_results', [
            'term' => $term->number,
            'date' => $date->toDateString(),
        ]);

        foreach ($response as $data) {
            $this->createOrUpdateVote($term, $date, $data);
        }
    }

    protected function createOrUpdateVote(Term $term, Carbon $date, array $data): Vote
    {
        $document = $this->findOrCreateDocument($data['reference']);

        $vote = Vote::create([
            'doceo_vote_id' => $data['doceo_vote_id'],
            'date' => $date,
            'description' => $data['description'],
            'term_id' => $term->id,
            'document_id' => $document->id,
        ]);

        return $vote;
    }

    protected function findOrCreateDocument(array $data): Document
    {
        $term = Term::whereNumber($data['term'])->first();

        return Document::firstOrCreate([
            'type' => DocumentTypeEnum::make($data['type']),
            'term_id' => $term->id,
            'number' => $data['number'],
            'year' => $data['year'],
        ]);
    }
}