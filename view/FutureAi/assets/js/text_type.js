// Text type animation

// Home 2


'use strict';

const type = document.querySelector('.type-text');

let listTexts = [
    " AI Security",
    "Tech Defense", 
    "Cyber Skills",
    "Protect Data",
    "Web Security",];

let index = 0;
let current = 0;

function typeTextFunc() {
    if (current === listTexts.length) {
        current = 0;
    }
    let typeText = setInterval(() => {
        if (index == listTexts[current].length - 1) {
            clearInterval(typeText);
            clearTextFunc();
        } else {
            index++;
            type.textContent += listTexts[current][index];
        }
    }, 150);
};

function clearTextFunc() {
    let clearText = setInterval(() => {
        if (type.textContent.length == 0) {
            clearInterval(clearText);
            index = -1;
            current++
            typeTextFunc();
        } else {
            type.textContent = type.textContent.slice(0, -1)
        }
    }, 150);
};

typeTextFunc();


// Home 3

// 'use strict';

// const type = document.querySelector('.type-text-one');

// let listTexts = ['Chatbot!', 'Talkbot', 'IMbot'];

// let index = -1;
// let current = 0;

// function typeTextFunc() {
//     if (current === listTexts.length) {
//         current = 0;
//     }
//     let typeText = setInterval(() => {
//         if (index == listTexts[current].length - 1) {
//             clearInterval(typeText);
//             clearTextFunc();
//         } else {
//             index++;
//             type.textContent += listTexts[current][index];
//         }
//     }, 150);
// };

// function clearTextFunc() {
//     let clearText = setInterval(() => {
//         if (type.textContent.length == 0) {
//             clearInterval(clearText);
//             index = -1;
//             current++
//             typeTextFunc();
//         } else {
//             type.textContent = type.textContent.slice(0, -1)
//         }
//     }, 150);
// };

// typeTextFunc();