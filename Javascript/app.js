// Update the content of the first <h1> element
document.querySelector('h1').innerHTML += ' Modified Title';

// Change CSS styles of the <h1> element
document.querySelector('h1').style.color = 'blue';
document.querySelector('h1').style.fontSize = '2em';

// Add a new paragraph element to the DOM
const newElement = document.createElement('p');
newElement.innerHTML = 'This is a new paragraph added to the DOM.';
document.body.appendChild(newElement);

// Update the content and style of the <h1> element
document.querySelector('h1').innerHTML = 'JavaScript DOM Manipulation';
document.querySelector('h1').style.color = 'blue';


console.dir(document);