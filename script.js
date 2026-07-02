
function mensaje(){
    alert("Bienvenido a Uni-Sano");
}

/* QUIZ */
function quiz(){

    let p1 = prompt("1. ¿El alcohol mejora la concentración? (si/no)");
    let p2 = prompt("2. ¿El alcohol puede causar adicción? (si/no)");
    let p3 = prompt("3. ¿El alcohol afecta el rendimiento académico? (si/no)");

    alert("Gracias por responder. Recuerda: el alcohol puede afectar tu salud y tus estudios.");
}

/* VERDADERO O FALSO */
function verdaderoFalso(){

    let p1 = prompt("1. El café elimina el efecto del alcohol. (verdadero/falso)");
    let p2 = prompt("2. Beber mucho alcohol puede dañar el hígado. (verdadero/falso)");
    let p3 = prompt("3. El alcohol ayuda a resolver problemas emocionales. (verdadero/falso)");

    alert("Respuestas correctas:\n1. Falso\n2. Verdadero\n3. Falso");
}

/* JUEGO DE DECISIONES */
function decision(){

    let p1 = prompt("Tus amigos te invitan a beber y mañana tienes examen. ¿Vas o estudias?");
    let p2 = prompt("Te sientes triste. ¿Buscas ayuda o bebes alcohol?");
    let p3 = prompt("En una fiesta te presionan para tomar. ¿Aceptas o dices no?");

    alert("Tus decisiones construyen tu futuro. Elegir bien hoy puede cambiar tu mañana.");
}


function respuesta(){

    let p1 = prompt("1. ¿Consumes alcohol para olvidar problemas? (si/no)");
    let p2 = prompt("2. ¿Has faltado a clases por consumir alcohol? (si/no)");
    let p3 = prompt("3. ¿Tus amigos te presionan para beber? (si/no)");
    let p4 = prompt("4. ¿Te cuesta divertirte sin alcohol? (si/no)");

    alert("Autoevaluación completada.\nSi respondiste 'sí' a varias preguntas, es importante reflexionar y buscar apoyo.");
}

function respuesta2(){

    let p1 = prompt("1. ¿Prefieres actividades sanas como deporte o estudio? (si/no)");
    let p2 = prompt("2. ¿Sabes decir NO cuando te ofrecen alcohol? (si/no)");
    let p3 = prompt("3. ¿Buscas ayuda cuando tienes problemas? (si/no)");

    alert("Excelente. Mantener hábitos saludables fortalece tu bienestar y tu futuro.");
}

/* MENU MOVIL */
function toggleMenu(){
    document.getElementById("menu").classList.toggle("active");
}

