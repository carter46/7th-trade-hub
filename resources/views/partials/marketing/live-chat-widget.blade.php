{{-- Live chat widget — Smartsupp or Jivo (only on Contact page). Keys from SystemSetting. --}}
@php
    $provider = strtolower(trim((string) \App\Models\SystemSetting::get('live_chat_provider', 'none')));
    $smartsuppKey = trim((string) \App\Models\SystemSetting::get('smartsupp_key', ''));
    $jivoWidgetId = trim((string) \App\Models\SystemSetting::get('jivo_widget_id', ''));

    if ($provider === '' || ! in_array($provider, ['smartsupp', 'jivo', 'none'], true)) {
        $provider = $smartsuppKey !== '' ? 'smartsupp' : 'none';
    }
@endphp

@if($provider === 'smartsupp' && $smartsuppKey !== '')
<script>
(function() {
  if (window.__tthLiveChatLoaded) return;
  window.__tthLiveChatLoaded = true;
  window._smartsupp = window._smartsupp || {};
  window._smartsupp.key = @json($smartsuppKey);
  window._smartsupp.widget = {
    colors: {
      primary: '#0B6A39',
      secondary: '#0F172A'
    }
  };
  window.smartsupp || (function(d) {
    var s, c, o = window.smartsupp = function() { o._.push(arguments); };
    o._ = [];
    s = d.getElementsByTagName('script')[0];
    c = d.createElement('script');
    c.type = 'text/javascript';
    c.charset = 'utf-8';
    c.async = true;
    c.src = 'https://www.smartsuppchat.com/loader.js?';
    s.parentNode.insertBefore(c, s);
  })(document);
})();
</script>
<noscript>Powered by <a href="https://www.smartsupp.com" target="_blank" rel="noopener">Smartsupp</a></noscript>
@elseif($provider === 'jivo' && $jivoWidgetId !== '')
@php
    $jivoId = $jivoWidgetId;
    if (preg_match('#code\.jivosite\.com/(?:script/)?widget/([A-Za-z0-9_-]+)#i', $jivoWidgetId, $m)) {
        $jivoId = $m[1];
    } elseif (preg_match('#(?:jv-id|data-jv-id)=[\'"]?([A-Za-z0-9_-]+)#i', $jivoWidgetId, $m)) {
        $jivoId = $m[1];
    } elseif (preg_match('#widget_id\s*=\s*[\'"]?([A-Za-z0-9_-]+)#i', $jivoWidgetId, $m)) {
        $jivoId = $m[1];
    }
    $jivoId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $jivoId);
@endphp
@if($jivoId !== '')
<script>
(function(){
  if (window.__tthLiveChatLoaded) return;
  window.__tthLiveChatLoaded = true;
  var s = document.createElement('script');
  s.src = @json('https://code.jivosite.com/widget/'.$jivoId);
  s.async = true;
  document.getElementsByTagName('head')[0].appendChild(s);
})();
</script>
@endif
@endif
