jQuery(function ($) {
  $("#eol-email-form").on("submit", function (e) {
    e.preventDefault();

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
        } else {
          $("#eol-msg").text(res.data);
        }
      },
    );
  });

  $("#eol-otp-form").on("submit", function (e) {
    e.preventDefault();

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
          $("#eol-msg").text(res.data);
        }
      },
    );
  });
});
