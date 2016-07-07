<?php

namespace Kommercio\Models\Role;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $role_permissions_table = 'role_permissions';
    protected $fillable = ['name'];

    private $_permissions;

    //Methods
    public function getPermissions()
    {
        if(!isset($this->_permissions)){
            $qb = DB::table($this->role_permissions_table)->where('role_id', $this->id);
            $this->_permissions = $qb->pluck('permission');
        }

        return $this->_permissions;
    }

    public function clearPermissions()
    {
        DB::table($this->role_permissions_table)->where('role_id', $this->id)->delete();
    }

    public function savePermissions($permissions)
    {
        $this->clearPermissions();

        foreach($permissions as $permission){
            DB::table($this->role_permissions_table)->insert([
                'role_id' => $this->id,
                'permission' => $permission
            ]);
        }
    }

    public function hasPermission($permission)
    {
        $permissions = $this->getPermissions();

        if(is_array($permission)){
            return $this->exists && array_intersect($permission, $permissions);
        }else{
            return $this->exists && in_array($permission, $permissions);
        }
    }

    //Relations
    public function users()
    {
        return $this->belongsToMany('Kommercio\Models\User');
    }

    //Statics
    public static function getRoleOptions()
    {
        $roles = self::orderBy('created_at', 'DESC')->pluck('name', 'id')->all();

        return $roles;
    }

    public static function getAvailablePermissions($key=null)
    {
        if($key){
            $permissions = config('permissions.'.$key, []);
        }else{
            $permissions = config('permissions', []);
        }

        return $permissions;
    }

    public static function getFlatPermissions()
    {
        $permissions = config('permissions', []);

        $return = [];

        foreach($permissions as $permissionGroup){
            $return = array_merge($return, $permissionGroup);
        }

        return $return;
    }
}
