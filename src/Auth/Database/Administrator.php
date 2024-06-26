<?php

namespace Encore\Admin\Auth\Database;

use \Encore\Admin\Contracts\Recordable;
use Encore\Admin\Traits\ContentTrait;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract, Recordable
{
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;
    use ContentTrait;
    use \Altek\Eventually\Eventually;
    use \Encore\Admin\Traits\Recordable;

    protected $fillable = ['email', 'password', 'name', 'avatar'];
    protected static $admin_permission_ids = [];
    protected static $admin_role_ids = [];
    protected $recordableExcludeColumns = ['avatar'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    protected static function initStatic()
    {
        static::$contentTitle = 'Administrator';
        static::$contentTitlePlural = 'Administrators';
        static::$contentSlug = 'system/administrators';
        static::$contentPermissionName = 'auth.management';
    }

    public static function boot()
    {
        parent::boot();

        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        foreach (config('admin.auth.admin_permissions') as $permission_slug) {

            $permission = $permissionModel::where('slug', $permission_slug)->first();
            if ($permission) {
                static::$admin_permission_ids[] = $permission->id;
            }
        }

        foreach (config('admin.auth.admin_roles') as $role_slug) {

            $role = $roleModel::where('slug', $role_slug)->first();
            if ($role) {
                static::$admin_role_ids[] = $role->id;
            }
        }

        static::attaching(function ($model, $relation, $properties) {
            $user = \Auth::guard('admin')->user();
            if (!$user || !\Auth::guard('admin')->user()->isAdministrator()){

                if ($relation === 'permissions'){
                    foreach ($properties as $property){
                        if (in_array($property['permission_id'], static::$admin_permission_ids)) return false;
                    }
                    
                }
    
                if ($relation === 'roles'){
                    foreach ($properties as $property){
                        if (in_array($property['role_id'], static::$admin_role_ids)) return false;
                    }
                }
            }


        });

    }

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar)
    {
        if (url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg';

        return admin_asset($default);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    public static function getContentBaseColumn(){
        return 'name';
    }
    
    public function supplyExtraExtended(string $event, array $properties, $user, $contentClass, $contentIdentifier): array
    {
        return [
            'contentReadebleIdentifier' => $properties[$contentClass::getContentBaseColumn()],
            'userReadebleIdentifier' => $user->contentReadableIdentifier,
        ];
    }
}
