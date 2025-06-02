<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/respo.css') }}">
    <link rel="icon" type="image/x-icon" href="{{asset('assets/images/Logo.svg')}}">



    <title>TickDot</title>
</head>
<body>
<div class="nav">
    <div class="content flex_lng">
        <a href="#">
            <img src="{{asset('assets/images/Logo.svg')}}">
        </a>
    </div>
</div>
<div class="banner">
    <div class="content flex_lng">
        <div class="text_content">
            <div class="title">{{__('messages.skip_the_line')}}<br>{{__('messages.straight_to_the_fun')}}</div>
            <div class="desc">
                {{__('messages.your_event')}}
            </div>
            <div class="btns flex_lng">
                <a href="#">
                    <div>
                        <img src="{{asset('assets/images/googlePlay.svg')}}">
                    </div>
                    <div class="flex-col">
                        <div>GET IT ON</div>
                        <div class="title_m">
                            Google Play
                        </div>
                    </div>
                </a>
                <a href="#">
                    <div>
                        <img src="{{asset('assets/images/appStore.svg')}}">
                    </div>
                    <div class="flex-col">
                        <div>Download on the</div>
                        <div class="title_m">
                            APP Store
                        </div>
                    </div>
                </a>
            </div>

        </div>
        <div class="img_container">
            <img src="{{asset('assets/images/mobile.png')}}">
        </div>

    </div>
</div>
<div class="footer">
    <div class="content">
        <div class="first_col">
            <div>Follow us</div>
            <div class="flex">
                <a href="#">
                    <img src="{{asset('assets/images/facebook.png')}}">
                </a>
                <a href="#">
                    <img src="{{asset('assets/images/insta.png')}}">
                </a>
            </div>

        </div>
        <div class="first_col">
            <div>Contact us</div>
            <a href="#" class="line">+974 000 000-00000</a>
            <a href="#" class="line">tickdot@info.com</a>

        </div>
        <div class="switch_lang">
            <img class="globe" src="{{asset('assets/images/globe.png')}}">
            <div class="text">{{ app()->getLocale() }}</div>
            <img src="{{asset('assets/images/arrow-down.png')}}" class="arrow">
            <div class="options">
                <div class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">en</div>
                <div class="{{ app()->getLocale() == 'ar' ? 'active' : '' }}">ar</div>
                <div class="{{ app()->getLocale() == 'kur' ? 'active' : '' }}">kur</div>
            </div>
        </div>



    </div>
</div>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
