<?php 
require_once '../init.php';

$error = false;
if (isset($_POST['user'])) {
	if ((($_POST['user']=='lumia') && ($_POST['pass']=='sdpLumia07')) || (($_POST['user']=='kiasmitor') && ($_POST['pass']=='4dragons$'))) {
		$_SESSION['admin']= 'hola';
		header('Location: '.urladmin.'usuarios.php');
		exit;
	} else {
		$error=true;
	}
}

include 'header.php'; 
?>
<div class="page page-center">
      <div class="container container-tight py-4">
        <div class="card card-md">
          <div class="card-body">
            <h2 class="h2 text-center mb-4">Login</h2>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">User</label>
				<input type="text" class="form-control <?=$error?'is-invalid':''?>" placeholder="user" name="user">
              </div>
              <div class="mb-2">
                <label class="form-label">
                  Contraseña
                </label>
                <div class="input-group input-group-flat">
				<input type="password" class="form-control <?=$error?'is-invalid':''?>" placeholder="Contraseña" id="pass" name="pass">
                  <span class="input-group-text">
                    <a onclick="if ($('#pass').attr('type')=='text') $('#pass').attr('type','password'); else $('#pass').attr('type','text');" class="link-secondary" data-bs-toggle="tooltip" aria-label="Show password" data-bs-original-title="Show password">
                      <!-- Download SVG icon from http://tabler.io/icons/icon/eye -->
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                      </svg>
                    </a>
                  </span>
				  <?=$error?'<div class="invalid-feedback">Usuario o contraseña inválidos</div>':''?>
                </div>
              </div>
              <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Enviar</button>
              </div>
            </form>
          </div>
        </div>
       </div>
    </div>
<?php include 'footer.php'; ?>