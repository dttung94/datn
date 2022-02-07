// moduled querySelector
function qs(selectEl){
    return document.querySelector(selectEl);
}

// select RGB inputs
let red = qs('#red'),
green = qs('#green'),
blue = qs('#blue');

// selet num inputs
let redNumVal = qs('#redNum'),
greenNumVal = qs('#greenNum'),
blueNumVal = qs('#blueNum');

// select Color Display
let colorDisplay = qs('#color-display');
let colorType = qs('#color-change');

// select labels
let redLbl = qs('label[for=redNum]'),
greenLbl = qs('label[for=greenNum]'),
blueLbl = qs('label[for=blueNum]');

let colorChange = $('#color-change');
let colorTypeVal = colorType.value;
let dataType = colorChange.find(':selected').data('type');
let colorHexCode = qs('#color-hex-code');
let colorWrapHexCode= qs('#color-wrap-hex-code')

// init display Colors
displayColors();
// init Color Vals
colorNumbrVals();
// init ColorSliderVals
initSliderColors();
// init Change Range Val
changeRangeNumVal();
// init Colors controls
colorSliders();
getRgb(colorChange.find(':selected').data('color'));
changeColorHexCode();

// display colors
function displayColors(){
    colorDisplay.style.backgroundColor = `rgb(${red.value}, ${green.value}, ${blue.value})`;
}

// initial color val when DOM is loaded
function colorNumbrVals(){
    redNumVal.value = red.value;
    greenNumVal.value = green.value;
    blueNumVal.value = blue.value;
}

// initial colors when DOM is loaded
function initSliderColors(){
    // label bg colors
    // redLbl.style.background = `rgb(${red.value},0,0)`;
    // greenLbl.style.background = `rgb(0,${green.value},0)`;
    // blueLbl.style.background = `rgb(0,0,${blue.value})`;

    // slider bg colors
    sliderFill(red);
    sliderFill(green);
    sliderFill(blue);

}

// Slider Fill offset
function sliderFill(clr){
    let val = (clr.value - clr.min) / (clr.max - clr.min);
    let percent = val * 100;

    // clr input
    if(clr === red){
        clr.style.background = `linear-gradient(to right, rgb(${clr.value},0,0) ${percent}%, rgb(255,0,0) 0%)`;
        $('#red::-webkit-slider-thumb').css('style', 'background : ' + `rgba(${clr.value},0,0,${percent/100}% )`);
        qs('#color').value = `rgb(${red.value}, ${green.value}, ${blue.value})`;
    } else if (clr === green) {
        clr.style.background = `linear-gradient(to right, rgb(0,${clr.value},0) ${percent}%, rgb(0,255,0) 0%)`;
        qs('#color').value = `rgb(${red.value}, ${green.value}, ${blue.value})`;
    } else if (clr === blue) {
        clr.style.background = `linear-gradient(to right, rgb(0,0,${clr.value}) ${percent}%, rgb(0,0,255) 0%)`;
        qs('#color').value = `rgb(${red.value}, ${green.value}, ${blue.value})`;
    }
}

// change range values by number input
function changeRangeNumVal(){

    // Validate number range
    redNumVal.addEventListener('change', ()=>{
        // make sure numbers are entered between 0 to 255
        if(redNumVal.value > 255){
            alert('cannot enter numbers greater than 255');
            redNumVal.value = red.value;
        } else if(redNumVal.value < 0) {
            alert('cannot enter numbers less than 0');
            redNumVal.value = red.value;
        } else if (redNumVal.value == '') {
            alert('cannot leave field empty');
            redNumVal.value = red.value;
            initSliderColors();
            displayColors();
            changeColorElement(dataType)
        } else {
            red.value = redNumVal.value;
            initSliderColors();
            displayColors();
            changeColorElement(dataType)
        }
    });

    // Validate number range
    greenNumVal.addEventListener('change', ()=>{
        // make sure numbers are entered between 0 to 255
        if(greenNumVal.value > 255){
            alert('cannot enter numbers greater than 255');
            greenNumVal.value = green.value;
        } else if(greenNumVal.value < 0) {
            alert('cannot enter numbers less than 0');
            greenNumVal.value = green.value;
        } else if(greenNumVal.value === '') {
            alert('cannot leave field empty');
            greenNumVal.value = green.value;
            initSliderColors();
            displayColors();
            changeColorElement(dataType)
        } else {
            green.value = greenNumVal.value;
            initSliderColors();
            displayColors();
            changeColorElement(dataType)
        }
    });

    // Validate number range
    blueNumVal.addEventListener('change', ()=>{
        // make sure numbers are entered between 0 to 255
        if (blueNumVal.value > 255) {
            alert('cannot enter numbers greater than 255');
            blueNumVal.value = blue.value;
        } else if (blueNumVal.value < 0) {
            alert('cannot enter numbers less than 0');
            blueNumVal.value = blue.value;
        } else if(blueNumVal.value === '') {
            alert('cannot leave field empty');
            blueNumVal.value = blue.value;
            initSliderColors();
            displayColors();
            changeColorElement(dataType)
        } else {
            blue.value = blueNumVal.value;
            initSliderColors();
            displayColors();
            changeColorElement(dataType);
        }
    });
}

function changeColorType() {
    let color = colorChange.find(':selected').data('color');
    colorTypeVal = colorType.value;
    dataType = colorChange.find(':selected').data('type');
    getRgb(color);
}

function changeColorHexCode() {
    getRgb(colorHexCode.value);
}

function changeColorElement(dataType) {
    if (dataType === 'color-shop') {
        $(".minute-col.working-time.col-shop-" + colorChange.val()).attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})` + "!important");
        $(".btn.btn-primary.shop-" + colorChange.val()).attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})` + "!important");
    } else {
        switch (colorTypeVal) {
            case 'rank-1':
                $(".background-1, .rank-1").css('background-color', `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'rank-8':
                $(".background-8, .rank-8").css('background-color', `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'rank-10':
                $(".background-10, .rank-10").css('background-color', `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'rank-5':
                $(".background-5, .rank-5").css('background-color', `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'none-none':
                $(".slot-booking-none div.info div.body, .active-slot div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'offline-accepted':
                $(".slot-booking-offline.status-accepted div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'online-accepted':
                $(".slot-booking-online.status-accepted div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'online-pending':
                $(".slot-booking-online.status-pending div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'online-pending-change':
                $(".slot-booking-online.status-pending-change div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'online-canceled':
                $(".slot-booking-online.status-canceled div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'online-updating':
                $(".slot-booking-online.status-updating div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'free-accepted':
                $(".slot-booking-free.status-accepted div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'free-confirming':
                $(".slot-booking-free.status-confirming div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'free-canceled':
                $(".slot-booking-free.status-canceled div.info div.body").attr('style', "background-color: " + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
            case 'background' :
                $('.background').attr('style', 'background-color: ' + `rgb(${red.value}, ${green.value}, ${blue.value})`);
                break;
        }
    }
}

function getRgb(str){
    if (str.indexOf("#") !== -1) {
        str = hexToRgb(str);
    }

    var match = str.match(/rgba?\((\d{1,3}), ?(\d{1,3}), ?(\d{1,3})\)?(?:, ?(\d(?:\.\d?))\))?/);

    if (match) {
        redNumVal.value = parseInt(match[1]);
        red.value = redNumVal.value;
        sliderFill(red);
        greenNumVal.value = parseInt(match[2]);
        green.value = greenNumVal.value;
        sliderFill(green);
        blueNumVal.value = parseInt(match[3]);
        blue.value = blueNumVal.value;
        sliderFill(blue);
        displayColors();
        colorHexCode.value = rgbToHex(parseInt(match[1]), parseInt(match[2]), parseInt(match[3]));
        colorWrapHexCode.innerHTML = colorHexCode.value;
    }
}

// Color Sliders controls
function colorSliders(){
    red.addEventListener('input', () => {
        displayColors();
        initSliderColors();
        changeRangeNumVal();
        colorNumbrVals();
        changeColorElement(dataType);
        colorHexCode.value = rgbToHex(parseInt(redNumVal.value), parseInt(greenNumVal.value), parseInt(blueNumVal.value));
        colorWrapHexCode.innerHTML = colorHexCode.value;
    });

    green.addEventListener('input', () => {
        displayColors();
        initSliderColors();
        changeRangeNumVal();
        colorNumbrVals();
        changeColorElement(dataType);
        colorHexCode.value = rgbToHex(parseInt(redNumVal.value), parseInt(greenNumVal.value), parseInt(blueNumVal.value));
        colorWrapHexCode.innerHTML = colorHexCode.value;
    });

    blue.addEventListener('input', () => {
        displayColors();
        initSliderColors();
        changeRangeNumVal();
        colorNumbrVals();
        changeColorElement(dataType);
        colorHexCode.value = rgbToHex(parseInt(redNumVal.value), parseInt(greenNumVal.value), parseInt(blueNumVal.value));
        colorWrapHexCode.innerHTML = colorHexCode.value;
    });
}

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? "rgb(" +  parseInt(result[1], 16) + "," + parseInt(result[2], 16) + "," + parseInt(result[3], 16) + ")" : null;
}

function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}
