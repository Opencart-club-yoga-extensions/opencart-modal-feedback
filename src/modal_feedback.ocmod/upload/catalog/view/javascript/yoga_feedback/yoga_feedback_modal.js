document.addEventListener('DOMContentLoaded', () => {
    $("#form-feedback").on("submit", function(event) {
        event.preventDefault();

        let url = document.getElementById('form-feedback').dataset.urlAction;
        $.ajax({
            url: url,
            type: "post",
            data: $("#form-feedback").find("input[type=text], input[type=tel], input[type=email], input[type=checkbox]:checked, input[type=radio]:checked, textarea"),
            dataType: "json",
            beforeSend: function() {
              $("#form-feedback").find("button[type=submit]").prop("disabled", true);
            },
            success: function(json) {
                  if (json["error"]) {
                    for (i in json["error"]) {
                        let element = $('#input-' + i.replace('_', '-'));
                        $(element).addClass('is-invalid');
                        //element.after('<div class="invalid-tooltip">' + json["error"][i] + '</div>');
                        element.siblings('.js-input-error').text('');
                        element.siblings('.js-input-error').text(json["error"][i]);
                    }
                  } else if (json["warning"]) {
                    for (i in json["error"]["warning"]) {
                          new Alert(json["error"]["warning"][i], "warning");
                    }
                  } else if (json["success"]) {
                    document.getElementById('form-feedback').classList.add('form-feedback-success');
                    // $.magnificPopup.close();
                    // new Alert(json["success"], "success");
                    // let successMessage = document.querySelector('.js-success-message');
                    // successMessage.style.display = 'block';
                    // successMessage.innerText = json["success"];

                  }
            },
            complete: function() {
                  $("#form-feedback").find("button[type=submit]").prop("disabled", false);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });

    $("#form-feedback input").on("input", function() {
        $(this).removeClass("is-invalid");
        $(this).siblings('.js-input-error').text('');
    });

});