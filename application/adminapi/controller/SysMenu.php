<?php
namespace app\adminapi\controller;
use app\adminapi\controller\Common;
use think\Db;
use app\adminapi\model\SysMenu as SysMenumodel;
class SysMenu extends Common{
    
    
    
    /**
     * @api {get} SysMenu/Index 
     * @apiDescription 
     * @apiName Index
     * @apiGroup SysMenu
     * @apiParam {string} name 菜单名称
     * @apiParam {string} permission 菜单权限标识
     * @apiParam {string} parent_id 父菜单ID
     * @apiParam {int} type 菜单类型
     * @apiParam {int} page 页码
     * @apiParam {int} pageSize 分页大小
     * @apiSuccess  code 返回200
     * @apiSampleRequest http://basedemo.weidong100.com/menu
     * @apiVersion 1.0.0
     */
    public function index()
    {
        $where = null;
        $Imodel = new SysMenumodel();
        $arr = [];
        if(input('role_id')){
            $arr = db('sys_role_menu')->where('role_id',input('role_id'))->column('menu_id');
        }
        $result = $Imodel->getList($where);
        $result = node_merge($result,$arr);
        if(!empty($arr)){
            $extra['authRoot'] = true;
        }else{
            $extra['authRoot'] = false;
        }
        return out_info(200,'获取成功',$result,0,$extra);
    }
    
    public function getUserSysMenu()
    {
        if(input('access/a')){
            $map['r.role_code'] = ['in',explode(input('access/a'))];
        }else{
            $roles = db('sys_role')
                   ->alias('r')
                   ->join(config('database.prefix').'sys_user_role ur','ur.role_id=r.role_id','left')
                   ->where('ur.user_id',session('admin_id'))
                   ->column('r.role_code');
            $map['r.role_code'] = ['in',$roles];
        }
        $list = db('sys_menu')
              ->alias('m')
              ->join(config('database.prefix').'sys_role_menu rm','rm.menu_id=m.menu_id','left')
              ->join(config('database.prefix').'sys_role r','rm.role_id=r.role_id','left')
              ->where('type', 0)
              ->where($map)->select();
        $map['type'] = 0;
        foreach($list as $k=>$v){
            $ac = db('sys_role_menu')
                ->alias('rm')
                ->join(config('database.prefix').'sys_role r','rm.role_id=r.role_id','left')
                ->where('rm.menu_id',$v['menu_id'])
                ->column('r.role_code');
            $list[$k]['access'] = $ac;
        }
        $list = node_merge($list);
        return json(['code'=>200,'data'=>$list]);
    }
    
     /**
     * @api {get} SysMenu/doLogin 
     * @apiDescription 登录
     * @apiName doLogin
     * @apiGroup SysMenu
     * @apiParam {string} username 用户名
     * @apiParam {string} password 用户密码

     * @apiSuccess  code 返回200
     * @apiSampleRequest http://basedemo.weidong100.com/adminapi/menu/doLogin
     * @apiVersion 1.0.0 
     */
    public function doLogin(){
        $username = input('get.username');
        $password = input('get.password');
        
        if($username == "" || $password == ""){
            return $this->error('请输入用户名或密码');
        }
        
        $hasUser = Db::name('user')->where('username',$username)->find();
        if (empty($hasUser['username'])){
            return $this->error("用户名不存在，请先注册");
        }
        if (md5($password) != $hasUser['password']){
            return $this->error("账号或密码错误");
        }
        return $this->success('登录成功',url('index/index'));
        $_SESSION['username'] = $username;
        //
        
    }
     /**
     * @api {put} SysMenu/Update 菜单更新
     * @apiDescription 这个接口是用来修改菜单管理的
     * @apiName Update
     * @apiGroup SysMenu
     * @apiParam {int}   id 获取点击修改的ID
     * @apiParam {string} name 菜单名称
     * @apiParam {string} permission 菜单权限标识
     * @apiParam {string} path 前段URL
     * @apiParam {int} parent_id 父菜单ID
     * @apiParam {string} icon 图标
     * @apiParam {string} component VUE界面
     * @apiParam {string} sort 排序值
     * @apiParam {int} type 菜单类型（0菜单 1按钮）
     * @apiSuccess  code 返回200
     * @apiSampleRequest http://basedemo.weidong100.com/menu
     * @apiVersion 1.0.0
     */
    public function update()
    {
        $Umodel = new SysMenumodel();
        $id = input('id');
        $data['title']       = input('title');
        $data['name']       = input('put.name');
        $data['permission'] = input('put.permission');
        $data['path']       = input('put.path');
        $data['parent_id']  = input('put.parent_id');
        $data['icon']       = input('put.icon');
        $data['component']  = input('put.component');
        $data['sort']       = input('put.sort');
        $data['type']       = input('put.type');
        $data['del_flag']   = input('put.del_flag');
        $result = $Umodel->updat($data,$id);
        
        return  out_info(200,'修改成功');
    }
    
     /**
     * @api {delete} SysMenu/Delete 菜单删除 
     * @apiDescription 这个接口是用来删除菜单管理的
     * @apiName DeleteSysMenu
     * @apiGroup SysMenu
     * @apiParam {int} id 获取点击修改的ID
     * @apiSuccess  code 返回200
     * @apiSampleRequest http://basedemo.weidong100.com/menu
     * @apiVersion 1.0.0
     */
    public function delete()
    {
        $Dmodel = new  SysMenumodel();
        $id = input('id');
        $result = $Dmodel->delet($id);
        if ($result){
             return  out_info(200,'删除成功');
        }else{
            return  out_info(500,'资源不存在，删除失败');
        }
         
    }
    
    
     /**
     * @api {post} SysMenu/Create 菜单添加 
     * @apiDescription 这个接口是用来添加菜单管理的
     * @apiName create
     * @apiGroup SysMenu
     * @apiParam {string} name 菜单名称
     * @apiParam {string} permission 菜单权限标识
     * @apiParam {string} path 前端URL
     * @apiParam {string} parent_id 父菜单ID
     * @apiParam {string} icon 图标
     * @apiParam {string} component VUE界面
     * @apiParam {string} sort 排序值
     * @apiParam {string} type 菜单类型（0菜单 1按钮）
     * @apiSuccess  code 返回200
     * @apiSampleRequest http://basedemo.weidong100.com/menu/create
     * @apiVersion 1.0.0 
     */
    public function create(){
        
       $Mmodel = new SysMenumodel();
       $data['title']      = input('title');
       $data['name']       = input('name');
       $data['menu_id']    = input('menu_id');
       $data['permission'] = input('permission');
       $data['path']       = input('path');
       $data['parent_id']  = input('parent_id');
       $data['icon']       = input('icon');
       $data['component']  = input('component');
       $data['sort']       = input('sort');
       $data['type']       = input('type');
       $result = $Mmodel->insertMe($data);
       return  out_info(200,'添加成功',$result,count($result));
    }
    /**
     * @api {get} SysMenu/read 查询 
     * @apiDescription       查询
     * @apiName read
     * @apiGroup SysMenu
     * @apiParam {int}   id  菜单ID
     * @apiSuccess  code     返回200
     * @apiSampleRequest http://basedemo.weidong100.com/menu
     * @apiVersion 1.0.0
     */
    public function read()
    {
        $Smodel = new SysMenumodel();
        $menu_id = input("id");
        $result = $Smodel->find($menu_id);
        return out_info(200,'查询成功',$result,count($result)); 
    }
}