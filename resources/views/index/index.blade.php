<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>嘿嘿嘿</title>
</head>
<body>
    <h1>商品展示</h1>
    <hr/><hr/>
<table border=1 >
    <tr>
        <td>ID</td>
        <td>商品名称</td>
        <td>商品价格</td>
        <td>商品数量</td>
        <td>操作</td>
    </tr>
    @foreach ($arr as $v)
        <tr>
            <td>{{ $v->goods_id }}</td>
            <td><a href="goodsdetail?goods_id={{ $v->goods_id }}">{{ $v->goods_name }}</a></td>
            <td>{{ $v->goods_selfprice }}</td>
            <td>{{ $v->goods_num }}</td>
            <td>
                [<a href="add/{{ $v->goods_id }}" class="del">加入购物车</a>]
                {{--[<a href="javascript:;" class="update">修改</a>]--}}
            </td>

        </tr>
    @endforeach
</table>
</body>
</html>
<script src="/js/jquery/jquery-1.12.4.min.js"></script>
<script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js "></script>
<script>
    wx.config({
//        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: "{{$jsconfig['appId']}}", // 必填，公众号的唯一标识
        timestamp: "{{$jsconfig['timestamp']}}", // 必填，生成签名的时间戳
        nonceStr: "{{$jsconfig['nonceStr']}}", // 必填，生成签名的随机串
        signature: "{{$jsconfig['signature']}}",// 必填，签名
        jsApiList: ['chooseImage','uploadImage','updateAppMessageShareData'] // 必填，需要使用的JS接口列表
    });

    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
        //分享给好友
        wx.updateAppMessageShareData({
            title: '这是一个XXX', // 分享标题
            desc: '该写什么好呢', // 分享描述
            link:  'http://1809lvmingjin.comcto.com/indexx', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://1809lvmingjin.comcto.com/images/a1.jpg', // 分享图标
            success: function () {
                // 设置成功
//                    console.log();
            }
        })
    });


    wx.onMenuShareAppMessage({
        title: '这是一个XXX', // 分享标题
        desc: '该写什么好呢', // 分享描述
        link: 'http://1809lvmingjin.comcto.com/indexx', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
        imgUrl: 'http://1809lvmingjin.comcto.com/images/a1.jpg', // 分享图标
        type: '', // 分享类型,music、video或link，不填默认为link
        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
        success: function () {
            alert(111);
        }
    });
</script>



