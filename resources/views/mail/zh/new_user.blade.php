<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ezwork有新用户注册</title>
  </head>
  <body>
    <p>有新用户注册</p>
    <p>邮箱： {{$user['email']}}</p>
    <p></p>
    <p>易和网，全球泛工业领域外贸综合服务平台</p>
    <p>
      <a href="{{env('MALL_URL')}}" target="_blank">{{env('MALL_URL_NO_HTTP')}}</a>
    </p>
    <p>
      <a href="{{env('MALL_URL')}}" target="_blank">
        <img style="width: 128px;" src="https://file05.erui.com/group1/M00/00/00/rBFIw2I65zKAHbkFAADxj2CkeEU808.png" />
      </a>
    </p>
  </body>
</html>
