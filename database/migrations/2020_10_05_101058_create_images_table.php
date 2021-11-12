<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Encore\Admin\Traits\MenuTrait;
use Encore\Admin\Traits\PermissionTrait;

class CreateImagesTable extends Migration
{
    use MenuTrait, PermissionTrait; 

    protected $model = \Encore\Admin\Models\Image::class;

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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('path', 190);
            $table->string('image_class', 190);
            $table->string('source', 150)->nullable()->default(NULL);
            $table->text('title')->nullable()->default(NULL);
            $table->integer('width')->nullable()->default(NULL)->index();
            $table->integer('height')->nullable()->default(NULL);
            $table->string('filename', 190)->nullable()->default(NULL);
            $table->string('extension', 10)->nullable()->default(NULL);
            $table->text('formats')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
        });

        $this->createMenu([
            'order' => 1,
            'title' => $this->namePlural,
            'icon' => 'fa-picture-o',
            'uri' => 'images',
            'permission' => 'images.show',
        ]);

        $this->addContentPermissions('images', $this->namePlural)->createRoleByPermissionSlug("{{$this->namePlural}} - {full access}", 'images_full_access', 'images.%');

        $this->updatePermission('images.show', [
            'http_path' => "\n" . '/images-modal/browse'
        ]);

        $this->updatePermission('images.create', [
            'http_path' => "\n" . '/images-modal/modal-form'
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->removeMenu('images');
        $this->removeContentPermissions('images');
        $this->removeRole('images_full_access');
        Schema::dropIfExists('images');
    }
}
