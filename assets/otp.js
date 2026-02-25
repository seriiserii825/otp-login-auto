jQuery(function ($) {
  function msg(text, isError) {
    $("#eol-msg").text(text).toggleClass("is-error", !!isError);
  }

  $("#eol-email-form").on("submit", function (e) {
    e.preventDefault();
    msg("");

    $.post(
      EOL.ajax,
      {
        action: "eol_send_otp",
        nonce: EOL.nonce,
        email: $(this).find("input").val(),
      },
      (res) => {
        if (res.success) {
          $("#eol-email-form").hide();
          $("#eol-otp-form").show();
          msg(res.data);
        } else {
          msg(res.data, true);
        }
      },
    ).fail((xhr) => {
      console.error("eol_send_otp failed", xhr.status, xhr.responseText);
      msg("Request failed (" + xhr.status + "). Check console.", true);
    });
  });

  $("#eol-otp-form").on("submit", function (e) {
    e.preventDefault();
    msg("");

    $.post(
      EOL.ajax,
      {
        action: "eol_verify_otp",
        nonce: EOL.nonce,
        otp: $(this).find("input").val(),
      },
      (res) => {
        if (res.success) {
          window.location = res.data.redirect;
        } else {
          msg(res.data, true);
        }
      },
    ).fail((xhr) => {
      console.error("eol_verify_otp failed", xhr.status, xhr.responseText);
      msg("Request failed (" + xhr.status + "). Check console.", true);
    });
  });
});
