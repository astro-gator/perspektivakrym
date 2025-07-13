<?php

namespace Modules\Perspektivakrym\Console;

use Illuminate\Console\Command;
use Modules\Perspektivakrym\Http\Controllers\UnisenderController;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ParseDealToUnisenderCommand extends Command
{
    protected $cUnisensder;
    protected $name = 'perspektivakrym:parse_to_unisender';
    protected $description = 'Парсер сделок в списки рассылок юнисендера';
    public function __construct(UnisenderController $unisenderController)
    {
        parent::__construct();
        $this->cUnisensder = $unisenderController;
    }

    public function handle()
    {
        $listId = config('perspektivakrym.lists.all_leads');
        $this->cUnisensder->getDealAllLead($listId);

        //Монако
        $listId = config('perspektivakrym.lists.interest_monako');
//        $this->cUnisensder->getDealInterested(92, $listId);
        $this->cUnisensder->getLeadInterested(54, $listId);
        // Паруса Мечты
        $listId = config('perspektivakrym.lists.interest_parusa_mechty');
//        $this->cUnisensder->getDealInterested(94, $listId);
        $this->cUnisensder->getLeadInterested(56, $listId);
        //  - Парк плаза
        $listId = config('perspektivakrym.lists.interest_park_plaza');
//        $this->cUnisensder->getDealInterested(102, $listId);
        $this->cUnisensder->getLeadInterested(64, $listId);
        //  - Династия
        $listId = config('perspektivakrym.lists.interest_dinastiya');
//        $this->cUnisensder->getDealInterested(96, $listId);
        $this->cUnisensder->getLeadInterested(58, $listId);
        //Лучи
        $listId = config('perspektivakrym.lists.interest_luchi');
        $this->cUnisensder->getLeadInterested(60, $listId);


        //Монако
        $listId = config('perspektivakrym.lists.bought_monako');
        $this->cUnisensder->getDealBought('Жилой комплекс «Монако»', $listId);
        // Паруса Мечты
        $listId = config('perspektivakrym.lists.bought_parusa_mechty');
        $this->cUnisensder->getDealBought('Паруса Мечты', $listId);
        //  - Парк плаза
        $listId = config('perspektivakrym.lists.bought_park_plaza');
        $this->cUnisensder->getDealBought('Парк Плаза', $listId);
        //  - Династия
        $listId = config('perspektivakrym.lists.bought_dinastiya');
        $this->cUnisensder->getDealBought('Династия', $listId);

        //  - Лучи
        $listId = config('perspektivakrym.lists.bought_luchi');
        $this->cUnisensder->getDealBought('Luchi', $listId);

        // Купившие Паруса Мечты, Жилой комплекс «Монако», Династия, LUCHI
        $listId = config('perspektivakrym.lists.bought_parusa_mechty_luchi_monako_dinastiya');
        $list = config('perspektivakrym.lists.bought_multiple');
        $this->cUnisensder->getDealBoughtForMultiple($list, $listId);
    }
}
