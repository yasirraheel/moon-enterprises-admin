<!-- Bootstrap core CSS -->
<link href="{{ asset('public/css/core.min.css') }}?v={{$settings->version}}" rel="stylesheet">
<link href="{{ asset('public/css/bootstrap.min.css') }}?v={{$settings->version}}" rel="stylesheet">
<link href="{{ asset('public/css/bootstrap-icons.css') }}?v={{$settings->version}}" rel="stylesheet">
<link href="{{ asset('public/js/fleximages/jquery.flex-images.css') }}" rel="stylesheet">
<link href="{{ asset('public/css/styles.css') }}?v={{$settings->version}}" rel="stylesheet">

@auth
@if ($settings->push_notification_status)
<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script>
  const myDeviceKeysId = {!! json_encode(auth()->user()->oneSignalDevices->pluck('player_id')->all()) !!};

  var OneSignal = window.OneSignal || [];
    var initConfig = {
      appId: "{{ $settings->onesignal_appid }}",
      autoResubscribe: true,
      safari_web_id: "web.onesignal.auto.0c986762-0fae-40b1-a5f6-ee95f7275a97",
      notifyButton: {
        enable: false,
      },
      welcomeNotification: {
        message: "{{ __('misc.notifications_activated_successfully') }}"
      },
      persistNotification: true,

      promptOptions: {
      slidedown: {
        prompts: [
          {
            type: "push", // current types are "push" & "category"
            autoPrompt: true,
            text: {
              /* limited to 90 characters */
              actionMessage: "{{ __('misc.push_notification_title', ['app' => $settings->title]) }}",
              /* acceptButton limited to 15 characters */
              acceptButton: "{{ __('misc.activate') }}",
              /* cancelButton limited to 15 characters */
              cancelButton: "{{ __('misc.maybe_later') }}"
            },
            delay: {
              pageViews: 1,
              timeDelay: 20
            }
          }
        ]
      }
    }
    // END promptOptions,
    };


  OneSignal.push(function () {
        OneSignal.SERVICE_WORKER_PARAM = { scope: '/public/js/' };
        OneSignal.SERVICE_WORKER_PATH = 'public/js/OneSignalSDKWorker.js'
        OneSignal.SERVICE_WORKER_UPDATER_PATH = 'public/js/OneSignalSDKWorker.js'
        OneSignal.init(initConfig);

        OneSignal.showSlidedownPrompt();
    });

  OneSignal.push(function() {

    // Get User Id
    OneSignal.getUserId(function(userId) {
      pushUserId = userId;

      if (pushUserId !== null) {
        var isRegisterDevice = $.inArray(pushUserId, myDeviceKeysId);
        if (isRegisterDevice === -1) {
          $.post("{{ url('api/device/register') }}", {player_id: pushUserId, user_id: {{ auth()->id() }} });
        }
      }
    });

    OneSignal.isPushNotificationsEnabled(function(isEnabled) {
    if (isEnabled)
      console.log("Push notifications are enabled!");
    else
      console.log("Push notifications are not enabled yet.");
  });

  // Subscription Change
	OneSignal.on("subscriptionChange",
  function(isSubscribed) {

    OneSignal.push(function() {
        OneSignal.getUserId(function(userId) {
          pushUserId = userId;

        if (isSubscribed == false) {
        $.get("{{ url('api/device/delete') }}", {player_id: pushUserId});
      } else {
            $.post("{{ url('api/device/register') }}", {player_id: pushUserId, user_id: {{ auth()->id() }} });
          }});

        });
      });
});
</script>
@endif

@endauth

<script type="text/javascript">
var URL_BASE = "{{ url('/') }}";
var lang = '{{ session('locale') }}';
var _title = '@section("title")@show {{Helper::titleSite()}}';
var session_status = "{{ auth()->check() ? 'on' : 'off' }}";
var colorStripe = '#000000';
var copiedSuccess = "{{ __('misc.copied_success') }}";
var error = "{{__('misc.error')}}";
var error_oops = "{{__('misc.error_oops')}}";
var resending_code = "{{__('misc.resending_code')}}";
var isProfile = {{ request()->route()->named('profile') ? 'true' : 'false' }};
var download = '{{__('misc.download')}}';
var downloading = '{{__('misc.downloading')}}';
var announcement_cookie = "{{$settings->announcement_cookie}}";
var ok = "{{__('misc.ok')}}";
var darkMode = "{{ __('misc.dark_mode') }}";
var lightMode = "{{ __('misc.light_mode') }}";

@auth
var stripeKey = "{{ PaymentGateways::where('id', 2)->where('enabled', '1')->first() ? env('STRIPE_KEY') : false }}";
var delete_confirm = "{{__('misc.delete_confirm')}}";
var confirm_delete = "{{ __('misc.yes') }}";
var cancel_confirm = "{{ __('misc.no') }}";
var your_subscribed = "{{__('misc.your_subscribed')}}";
var formats_available = "{{ __('misc.formats_available') }}";
var max_size_upload = "{{__('misc.max_size_upload').' '.Helper::formatBytes(1048576)}}";
var thanks = "{{ __('misc.thanks') }}";
@endauth
</script>

<style type="text/css">

@if ($settings->custom_css)
  {!! $settings->custom_css !!}
@endif

.home-cover { background-image: url('{{ url('public/img', $settings->image_header) }}') }
:root {
  --color-default: {{ $settings->color_default }} !important;
  --bg-auth: url('{{ url('public/img', $settings->image_header) }}');
}
</style>
