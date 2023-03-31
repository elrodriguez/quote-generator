<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bootstrap demo</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-aFq/bzH65dt+w6FI2ooMVUpc+21e0SRygnTpmBvdBgSdnuTN7QbdgL+OapgHtvPp" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <h1>Generador de citas</h1>
            <form id="form-citar" action="{{ route('citar') }}" method="POST">
                <div class="row mb-4 g-3 align-items-center">
                    <div class="col-3">
                      <label for="url" class="col-form-label text-right">DOI del artículo:</label>
                    </div>
                    <div class="col-9">
                        <input class="form-control" type="text" id="url" name="url" required value="10.17993/ingytec.2018.45">
                    </div>
                </div>
                <div class="row mb-4 g-3 align-items-center">
                    <div class="col-3">
                      <label for="normativa" class="col-form-label text-right">Normativa de citación:</label>
                    </div>
                    <div class="col-9">
                        <select class="form-select" id="normativa" name="normativa" required>
                            <option value="apa">APA</option>
                            <option value="mla">MLA</option>
                            <option value="harvard">Harvard</option>
                            <option value="iso690">ISO690</option>
                            <option value="ieee">IEEE</option>
                            <option value="chicago">Chicago</option>
                            <option value="vancouver">Vancouver</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-9 offset-md-3">
                        <input type="submit" class="btn btn-primary" value="Generar cita">
                    </div>
                </div>
            </form>
            <div class="mt-4" id="divRes" style="display:none">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Resultado</h5>
                        <button class="btn btn-outline-secondary btn-sm ml-auto" id="copiar">
                            <i class="bi bi-clipboard"></i> Copiar
                          </button>
                    </div>
                    <div class="card-body">
                        <div id="respuesta"></div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" integrity="sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js" integrity="sha384-qKXV1j0HvMUeCBQ+QVp7JcfGl760yU08IQ+GpUo5hlbpg51QRiuqHAJz8+BrxE/N" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                
                const form = document.querySelector('#form-citar');
                const respuesta = document.querySelector('#respuesta');
        
                form.addEventListener('submit', function(event) {
                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Por favor, espere mientras se carga el contenido.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    event.preventDefault();
        
                    const xhr = new XMLHttpRequest();
        
                    xhr.open('POST', form.getAttribute('action'), true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            document.getElementById('divRes').style.display = 'block';
                            respuesta.innerHTML = JSON.parse(xhr.responseText).cita;
                        } else {
                            document.getElementById('divRes').style.display = 'none';
                            Swal.fire('Error al obtener la cita.');
                        }
                        Swal.close();
                    };
        
                    xhr.onerror = function() {
                        document.getElementById('divRes').style.display = 'none';
                        Swal.fire('Error al obtener la cita.');
                    };
        
                    xhr.send(new FormData(form));
                });
            });

            document.getElementById("copiar").addEventListener("click", function() {
                let copyText = document.getElementById('respuesta').innerText;
                if (copyText) {
                    const textArea = document.createElement('textarea');
                    textArea.textContent = copyText;
                    document.body.append(textArea);       
                    textArea.select();       
                    document.execCommand("copy");  
                    Swal.fire('Copiado correctamente');
                    textArea.remove() ;
                } else {
                    Swal.fire('No hay contenido para copiar.');
                }
            });
        </script>
        
    </body>
</html>