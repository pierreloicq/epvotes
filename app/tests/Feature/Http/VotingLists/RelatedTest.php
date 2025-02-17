<?php

use App\Enums\VoteTypeEnum;
use App\Vote;
use App\VoteCollection;
use App\VotingList;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->voteCollection = VoteCollection::factory([
        'title' => 'The title',
    ])->create();

    $this->finalVotingList = VotingList::factory([
        'id' => 1,
        'vote_id' => Vote::factory([
            'vote_collection_id' => $this->voteCollection,
            'type' => VoteTypeEnum::PRIMARY(),
            'final' => true,
        ]),
    ]);

    $this->nonFinalVotingList = VotingList::factory([
        'id' => 2,
        'vote_id' => Vote::factory([
            'vote_collection_id' => $this->voteCollection,
            'type' => VoteTypeEnum::SEPARATE(),
            'subject' => '§ 3',
            'split_part' => '1',
        ]),
    ]);

    $this->votingListWithoutVote = VotingList::factory([
        'id' => 3,
        'vote_id' => null,
    ]);
});

it('renders successfully for final votes', function () {
    $this->finalVotingList->create();
    $response = $this->get('/votes/1/related');

    expect($response)->toHaveStatus(200);
});

it('returns 404 for non-final votes', function () {
    $this->nonFinalVotingList->create();
    $response = $this->get('/votes/2/related');

    expect($response)->toHaveStatus(404);
});

it('returns 404 for voting list without vote', function () {
    $this->votingListWithoutVote->create();
    $response = $this->get('/votes/3/related');

    expect($response)->toHaveStatus(404);
});

it('contains voting list title', function () {
    $this->finalVotingList->create();
    $response = $this->get('/votes/1/related');

    expect($response)->toHaveSelectorWithText('h1', 'The title');
});

it('contains list of related votes', function () {
    $this->finalVotingList->create();
    $this->nonFinalVotingList->create();
    $response = $this->get('/votes/1/related');

    expect($response)->toHaveSelectorWithText('article', 'Separate vote on § 3/1');
});
