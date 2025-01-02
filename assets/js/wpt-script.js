jQuery(document).ready(function ($) {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        $.post(wcCurrencySwitcher.ajax_url, {
          action: "wpt_set_user_location",
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        });
      },
      function (error) {
        console.error("Geolocation error:", error.message);
      }
    );
  } else {
    console.warn("Geolocation is not supported by this browser.");
  }
});
