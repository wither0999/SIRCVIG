/**
 * Sistema SIRCVIG - Validaciones y Lógica Frontend
 */

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // 1. MÁSCARAS Y VALIDACIONES DE ENTRADA
    // ==========================================

    // Inputs numéricos (Cédula, Teléfono)
    const numberInputs = document.querySelectorAll('input[data-type="number"], input[name="cedula"], input[name="telefono"]');

    numberInputs.forEach(input => {
        // Prevenir entrada de no-números
        input.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            validarInput(this);
        });

        // Validar al perder el foco
        input.addEventListener('blur', function () {
            validarInput(this);
        });
    });

    // Función genérica de validación visual
    function validarInput(input) {
        if (input.hasAttribute('required') && input.value.trim() === '') {
            marcarError(input, 'Este campo es obligatorio');
        } else if (input.name === 'cedula' && input.value.length < 6) {
            marcarError(input, 'Cédula inválida (mín. 6 dígitos)');
        } else if (input.name === 'telefono' && input.value.length > 0 && input.value.length < 10) {
            marcarError(input, 'Teléfono incompleto');
        } else {
            marcarExito(input);
        }
    }

    function marcarError(input, mensaje) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');

        // Buscar o crear mensaje de error
        let errorDiv = input.nextElementSibling;
        if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
        }
        errorDiv.textContent = mensaje;
    }

    function marcarExito(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');

        // Eliminar mensaje de error si existe
        let errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
            errorDiv.remove();
        }
    }

    // ==========================================
    // 2. DIRECCIÓN CASCADA (Manejado ahora por venezuela.js)
    // ==========================================
    // La lógica de dirección se ha movido a venezuela.js para evitar duplicidad y mejorar la estructura.

    // ==========================================
    // 3. INTERCEPTAR ENVÍO DE FORMULARIO (Obsoleto)
    // ==========================================
    // El backend ahora maneja los campos individuales (estado, municipio, parroquia).

    // ==========================================
    // 3. INTERCEPTAR ENVÍO DE FORMULARIO
    // ==========================================
    const formsWithAddress = document.querySelectorAll('form');

    formsWithAddress.forEach(form => {
        form.addEventListener('submit', function (e) {
            const estado = document.getElementById('estado');
            const municipio = document.getElementById('municipio');
            const parroquia = document.getElementById('parroquia');
            const detalle = document.getElementById('direccion_detalle');
            const direccionHidden = document.getElementById('direccion_hidden');

            // Si existen los campos de dirección desglosada
            if (estado && municipio && parroquia && detalle && direccionHidden) {
                if (estado.value && municipio.value && parroquia.value) {
                    // Concatenar dirección para enviar al backend
                    // Formato: ESTADO, MUNICIPIO, PARROQUIA, DETALLE
                    direccionHidden.value = `${estado.value}, ${municipio.value}, ${parroquia.value}, ${detalle.value}`;
                } else {
                    // Si el usuario no seleccionó todo, intentar enviar lo que haya o mostrar error
                    // Aquí asumimos que son requeridos si están presentes
                    if (!direccionHidden.value && detalle.value) {
                        // Fallback si solo llenó detalle (legacy support)
                        direccionHidden.value = detalle.value;
                    }
                }
            }
        });
    });

    // ==========================================
    // 4. CONFIRMACIÓN AL CANCELAR
    // ==========================================
    const cancelButtons = document.querySelectorAll('.btn-secondary');
    cancelButtons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes('cancelar')) {
            btn.addEventListener('click', function (e) {
                if (!confirm('¿Está seguro de que desea cancelar? Se perderán los datos no guardados.')) {
                    e.preventDefault();
                }
            });
        }
    });

    // ==========================================
    // 5. LOGOUT CONFIRMACIÓN
    // ==========================================
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            if (!confirm('¿Está seguro de que desea cerrar la sesión?')) {
                e.preventDefault();
            }
        });
    }

});
