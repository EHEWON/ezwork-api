<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unbind bank card</title>
  </head>
  <body>
    <p>Hello <strong>{{$user['email']}}</strong>,</p>
    <p>You are requesting to unbind your bank card, and the verification code is valid for {{$user['expired']}}，please use it as soon as possible. Please ignore this email if you are not doing it yourself.</p>
    <strong>{{$user['code']}}</strong>
    <p></p>
    <p>EHEWON, your global pan-industrial digital supply chain service platform.</p>
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
