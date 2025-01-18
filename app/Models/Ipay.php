<?php

namespace Modules\Ipay\Models;

use Modules\Base\Models\BaseModel;
use Illuminate\Database\Schema\Blueprint;

class Ipay extends BaseModel
{

    /**
     * The fields that can be filled
     *
     * @var array<string>
     */
    protected $fillable = ['item_id', 'status', 'txncd', 'ivm', 'qwh', 'afd', 'poi', 'uyt', 'ifd', 'agd', 'mc', 'p1', 'p2', 'p3', 'p4', 'payment_id', 'is_processed'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "ipay_ipay";

    public function migration(Blueprint $table): void
    {
        $table->id();

        $table->string('name');
        $table->string('description')->nullable();
        $table->string('published')->nullable();
        $table->string('item_id')->nullable();
        $table->string('status')->nullable();
        $table->string('txncd')->nullable();
        $table->string('ivm')->nullable();
        $table->string('qwh')->nullable();
        $table->string('afd')->nullable();
        $table->string('poi')->nullable();
        $table->string('uyt')->nullable();
        $table->string('ifd')->nullable();
        $table->string('agd')->nullable();
        $table->string('mc')->nullable();
        $table->string('p1')->nullable();
        $table->string('p2')->nullable();
        $table->string('p3')->nullable();
        $table->string('p4')->nullable();
        $table->foreignId('payment_id')->nullable()->constrained(table: 'account_payment')->onDelete('set null');
        $table->boolean('is_processed')->nullable();

    }
}
