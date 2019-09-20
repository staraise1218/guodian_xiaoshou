<?php
namespace app\admin\validate;
use think\validate;
//品牌验证器
class Series extends validate
{

    protected $rule=[
        ['name' ,'require|checkName'],
        ['brand_id' ,'require'],
        ['url' ,'url'],
        ['sort','number'],
        ['desc' ,'max:100']
    ];
    protected $message = [
        'name.require'      => '系列名称必须',
        'brand_id.require'  => '品牌ID必须存在',
        'name.checkName'    => '系列已经存在',
        'sort.number'       => '排序必须是数字',
        'desc.max'          => '系列描述不得大于100个字节'
    ];

    /**
     * 验证系列名称是否唯一
     * @param $value
     * @param $rule
     * @param $data
     */
    protected function checkName($value,$rule,$data){
        $data['id'] = empty($data['id'])?0:$data['id'];
        $checkBrandWhere = [
            'id'=>['neq',$data['id']],
            'name'=>$value,
            'brand_id'=>$data['brand_id']
        ];
        $res = M('Series')->where($checkBrandWhere)->count();
        return !empty($res) ? false:true;
    }
}