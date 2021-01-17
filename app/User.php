<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * このユーザが所有する投稿。（ Micropostモデルとの関係を定義）
     */
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function loadRelationshipCounts() {
        $this->loadCount('microposts', 'followings', 'followers', 'favoritings');
    }
    
    public function followings() {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers() {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId) {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if($exist || $its_me) {
            return false;
        }else{
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId) {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if($exist && !$its_me) {
            $this->followings()->detach($userId);
            return true;
        }else{
            return false;
        }
    }
    
    public function is_following($userId) {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts() {
        $userIds = $this->followings()->pluck('users.id')->toArray();
        //ユーザのidも配列に追加
        $userIds[] = $this->id;
        return Micropost::whereIn('user_id', $userIds);
    }
    
    public function favoritings() {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    public function favorite($micropostId) {
        $exist = $this->is_favoriting($micropostId);
        
        if($exist) {
            return false;
        }else{
            $this->favoritings()->attach($micropostId);
            return true;
        }
    }
    
    public function unfavorite($micropostId) {
        $exist = $this->is_favoriting($micropostId);
        
        if($exist) {
            $this->favoritings()->detach($micropostId);
            return true;
        }else{
            return false;
        }
    }
    
    
    public function is_favoriting($micropostId) {
        return $this->favoritings()->where('micropost_id', $micropostId)->exists();
    }
}
