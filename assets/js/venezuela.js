// Venezuela Data - Estados, Municipios y Parroquias
// Estructura simplificada para el ejemplo, enfocada en Anzoátegui completo
const venezuela = [
    {
        estado: "Anzoátegui",
        municipios: [
            {
                municipio: "Simón Rodríguez",
                parroquias: ["Edmundo Barrios", "Miguel Otero Silva"]
            },
            {
                municipio: "Anaco",
                parroquias: ["Anaco", "San Joaquín"]
            },
            {
                municipio: "Bolívar",
                parroquias: ["El Carmen", "San Cristóbal", "Bergantín", "Caigua", "El Pilar", "Naricual"]
            },
            {
                municipio: "Sotillo",
                parroquias: ["Puerto La Cruz", "Pozuelos"]
            },
            {
                municipio: "Guanipa",
                parroquias: ["San José de Guanipa"]
            },
            {
                municipio: "Freites",
                parroquias: ["Cantaura", "Santa Rosa", "Urica", "Libertador"]
            }
        ]
    },
    {
        estado: "Bolívar",
        municipios: [
            {
                municipio: "Caroní",
                parroquias: ["Cachamay", "Chirica", "Dalla Costa", "11 de Abril", "Simón Bolívar", "Unare", "Universidad", "Vista al Sol", "Pozo Verde", "Yocoima", "5 de Julio"]
            },
            {
                municipio: "Angostura del Orinoco",
                parroquias: ["Catedral", "Agua Salada", "La Sabanita", "Vista Hermosa", "Marhuanta", "José Antonio Páez", "Orinoco", "Panapana", "Zea"]
            }
        ]
    },
    {
        estado: "Distrito Capital",
        municipios: [
            {
                municipio: "Libertador",
                parroquias: ["Altagracia", "Antímano", "Candelaria", "Caricuao", "Catedral", "Coche", "El Junquito", "El Paraíso", "El Recreo", "El Valle", "La Pastora", "La Vega", "Macarao", "San Agustín", "San Bernardino", "San José", "San Juan", "San Pedro", "Santa Rosalía", "Santa Teresa", "Sucre (Catia)", "23 de Enero"]
            }
        ]
    },
    {
        estado: "Miranda",
        municipios: [
            {
                municipio: "Sucre",
                parroquias: ["Petare", "Leoncio Martínez", "Caucagüita", "Filas de Mariche", "La Dolorita"]
            },
            {
                municipio: "Chacao",
                parroquias: ["Chacao"]
            },
            {
                municipio: "Baruta",
                parroquias: ["Baruta", "El Cafetal", "Las Minas"]
            }
        ]
    }
    // Se pueden agregar más estados según necesidad
];

// Lógica de llenado
document.addEventListener('DOMContentLoaded', function() {
    const estadoSel = document.getElementById('estado');
    const munSel = document.getElementById('municipio');
    const parrSel = document.getElementById('parroquia');
    
    // Cargar Estados
    venezuela.forEach(est => {
        const opt = document.createElement('option');
        opt.value = est.estado;
        opt.textContent = est.estado;
        estadoSel.appendChild(opt);
    });
    
    // Al cambiar Estado
    estadoSel.addEventListener('change', function() {
        // Limpiar
        munSel.innerHTML = '<option value="">Seleccione Municipio</option>';
        munSel.disabled = true;
        parrSel.innerHTML = '<option value="">Seleccione Parroquia</option>';
        parrSel.disabled = true;
        
        const estVal = this.value;
        if (estVal) {
            const estadoObj = venezuela.find(e => e.estado === estVal);
            if (estadoObj) {
                // Habilitar y llenar Municipios
                munSel.disabled = false;
                estadoObj.municipios.forEach(mun => {
                    const opt = document.createElement('option');
                    opt.value = mun.municipio;
                    opt.textContent = mun.municipio;
                    munSel.appendChild(opt);
                });
            }
        }
    });
    
    // Al cambiar Municipio
    munSel.addEventListener('change', function() {
        // Limpiar Parroquia
        parrSel.innerHTML = '<option value="">Seleccione Parroquia</option>';
        parrSel.disabled = true;
        
        const estVal = estadoSel.value;
        const munVal = this.value;
        
        if (estVal && munVal) {
            const estadoObj = venezuela.find(e => e.estado === estVal);
            if (estadoObj) {
                const munObj = estadoObj.municipios.find(m => m.municipio === munVal);
                if (munObj) {
                    // Habilitar y llenar Parroquias
                    parrSel.disabled = false;
                    munObj.parroquias.forEach(parr => {
                        const opt = document.createElement('option');
                        opt.value = parr;
                        opt.textContent = parr;
                        parrSel.appendChild(opt);
                    });
                }
            }
        }
    });

    // Auto-selección para edición (usando data-selected atributos si existen)
    // Este código se ejecuta si los atributos están presentes
    setTimeout(() => {
        const oldEstado = estadoSel.getAttribute('data-selected');
        if (oldEstado) {
            estadoSel.value = oldEstado;
            estadoSel.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                const oldMun = munSel.getAttribute('data-selected');
                if (oldMun) {
                    munSel.value = oldMun;
                    munSel.dispatchEvent(new Event('change'));
                    
                    setTimeout(() => {
                        const oldParr = parrSel.getAttribute('data-selected');
                        if (oldParr) {
                            parrSel.value = oldParr;
                        }
                    }, 50);
                }
            }, 50);
        }
    }, 200);
});
