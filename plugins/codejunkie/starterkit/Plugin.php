<?php namespace Codejunkie\Starterkit;

use System\Classes\PluginBase;
use Rainlab\User\Models\User;
use Rainlab\User\Models\UserGroup;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Codejunkie\Starterkit\Components\Authentication' => 'Authentication'
        ];
    }

    public function registerSettings()
    {
    }

    public function boot()
    {
        User::extend(function($model) {
            $model->addDynamicMethod('addUserGroup', function($group) use ($model) {
                if ($group instanceof Collection) {
                    return $model->groups()->saveMany($group);
                }

                if (is_string($group)) {
                    $group = UserGroup::whereCode($group)->first();
                    return $model->groups()->save($group);
                }

                if ($group instanceof UserGroup) {
                    return $model->groups()->save($group);
                }
            });
        });
    }
}
