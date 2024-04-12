<?php

namespace Modules\Ipay\Entities;

use Illuminate\Database\Schema\Blueprint;
use Modules\Base\Entities\BaseModel;

class Ipay extends BaseModel
{
    /**
     * The fields that can be filled
     *
     * @var array<string>
     */
    protected $fillable = ['item_id', 'status', 'txncd', 'ivm', 'qwh', 'afd', 'poi', 'uyt', 'ifd', 'agd', 'mc', 'p1', 'p2', 'p3', 'p4', 'payment_id', 'is_processed'];

    /**
     * The fields that are to be render when performing relationship queries.
     *
     * @var array<string>
     */
    public $rec_names = ['item_id', 'txncd'];

    /**
     * List of tables names that are need in this model during migration.
     *
     * @var array<string>
     */
    public array $migrationDependancy = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "Ipay";

    /**
     * List of fields to be migrated to the datebase when creating or updating model during migration.
     *
     * @param Blueprint $table
     * @return void
     */
    public function fields(Blueprint $table = null): void
    {
        $this->fields = $table ?? new Blueprint($this->table);

        $this->fields->increments('id')->html('hidden');
        $this->fields->string('name')->html('text');
        $this->fields->string('description')->html('textarea');
        $this->fields->string('published')->html('switch');
        $this->fields->string('item_id')->nullable()->html('text');
        $this->fields->string('status')->nullable()->html('text');
        $this->fields->string('txncd')->nullable()->html('text');
        $this->fields->string('ivm')->nullable()->html('text');
        $this->fields->string('qwh')->nullable()->html('text');
        $this->fields->string('afd')->nullable()->html('text');
        $this->fields->string('poi')->nullable()->html('text');
        $this->fields->string('uyt')->nullable()->html('text');
        $this->fields->string('ifd')->nullable()->html('text');
        $this->fields->string('agd')->nullable()->html('text');
        $this->fields->string('mc')->nullable()->html('text');
        $this->fields->string('p1')->nullable()->html('text');
        $this->fields->string('p2')->nullable()->html('text');
        $this->fields->string('p3')->nullable()->html('text');
        $this->fields->string('p4')->nullable()->html('text');
        $this->fields->foreignId('payment_id')->nullable()->html('recordpicker')->relation(['payment']);
        $this->fields->boolean('is_processed')->nullable()->html('switch');
    }

    /**
     * List of structure for this model.
     */
    public function structure($structure): array
    {
        $structure['table'] = ['item_id', 'status', 'txncd', 'ivm', 'qwh', 'afd', 'poi', 'uyt', 'ifd', 'agd', 'mc', 'p1', 'p2', 'p3', 'p4', 'payment_id', 'is_processed'];
        $structure['filter'] = ['item_id', 'txncd'];

        return $structure;
    }


    /**
     * Define rights for this model.
     *
     * @return array
     */
    public function rights(): array
    {
        $rights = parent::rights();

        $rights['staff'] = ['view' => true];
        $rights['registered'] = ['view' => true];
        $rights['guest'] = [];

        return $rights;
    }
}
