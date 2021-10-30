<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Encore\Admin\Traits\MenuTrait;
use Encore\Admin\Traits\PermissionTrait;

class CreateImagesTable extends Migration
{
    use MenuTrait, PermissionTrait; 
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
            'title' => 'Képek',
            'icon' => 'fa-picture-o',
            'uri' => 'images',
            'permission' => 'images.index',
        ]);

        $this->addContentPermissions('images', 'Képek')->createRoleByPermissionSlug('Képek - teljes hozzáférés', 'images_full_access', 'images.%');

        $this->updatePermission('images.index', [
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
