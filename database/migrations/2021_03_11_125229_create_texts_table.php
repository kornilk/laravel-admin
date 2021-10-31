<?php

use Encore\Admin\Traits\MenuTrait;
use Encore\Admin\Traits\PermissionTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTextsTable extends Migration
{
    use MenuTrait, PermissionTrait; 

    protected $model = \Encore\Admin\Models\Text::class;
    
    public function __construct()
    {
        $this->namePlural = $this->model::getContentTitlePlural();
        $this->slug = $this->model::getContentSlug();
    }   

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('texts', function (Blueprint $table) {
            $table->id();
            $table->string('context', 190);
            $table->string('placeholder', 190);
            $table->text('value')->nullable()->default(NULL);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['context', 'placeholder'], 'context_placeholder');
        });

        $this->createMenu([
            'order' => 1,
            'title' => $this->namePlural,
            'icon' => 'fa-file-text-o',
            'uri' => 'texts',
            'permission' => 'texts.show',
        ]);

        $this->addContentPermissions('texts', $this->namePlural)->createRoleByPermissionSlug("{{$this->namePlural}} - {full access}", "texts_full_access", 'texts.%');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->removeMenu('texts');
        $this->removeContentPermissions('texts');
        $this->removeRole('texts_full_access');
        Schema::dropIfExists('texts');
    }
}
