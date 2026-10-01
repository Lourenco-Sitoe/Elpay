@php
$__pixels = collect();
try {
    if (!empty($product->producer_id)) {
        $__pixels = \App\Models\Pixel::where('producer_id', $product->producer_id)->where('active', true)->get();
    }
} catch (\Throwable) {}
@endphp
@foreach($__pixels as $__px)
@if($__px->platform === 'facebook')
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $__px->pixel_id }}');fbq('track','PageView');@if(!empty($purchaseValue))fbq('track','Purchase',{value:{{ (float)$purchaseValue }},currency:'MZN'});@endif</script>
@elseif($__px->platform === 'tiktok')
<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.load=function(e){var n="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=n;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=t||{};var o=document.createElement("script");o.type="text/javascript";o.async=!0;o.src=n+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('{{ $__px->pixel_id }}');ttq.page();@if(!empty($purchaseValue))ttq.track('CompletePayment',{value:{{ (float)$purchaseValue }},currency:'MZN'});@endif}(window,document,'ttq');</script>
@elseif($__px->platform === 'google')
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $__px->pixel_id }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','{{ $__px->pixel_id }}');@if(!empty($purchaseValue))gtag('event','purchase',{value:{{ (float)$purchaseValue }},currency:'MZN'});@endif</script>
@endif
@endforeach
