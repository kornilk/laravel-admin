<?php

use Encore\Admin\Traits\MenuTrait;
use Encore\Admin\Traits\PermissionTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHtmlTextsTable extends Migration
{
    use MenuTrait, PermissionTrait;

    protected $model = \Encore\Admin\Models\HtmlText::class;

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
        Schema::create('html_texts', function (Blueprint $table) {
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
            'icon' => 'fa-html5',
            'uri' => 'html-texts',
            'permission' => 'html-texts.show',
        ]);

        $this->addContentPermissions('html-texts', $this->namePlural)->createRoleByPermissionSlug("{{$this->namePlural}} - {full access}", 'html-texts_full_access', 'html-texts.%');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->removeMenu('html-texts');
        $this->removeContentPermissions('html-texts');
        $this->removeRole('html-texts_full_access');
        Schema::dropIfExists('html_texts');
    }
}
