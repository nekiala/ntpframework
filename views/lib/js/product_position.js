/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var dragSrcEl = null, currentId, moveId;

function handleDragStart(e) {
    this.style.opacity = '0.4';  // this / e.target is the source node.

    dragSrcEl = this;

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

var cols = document.querySelectorAll('.table tr.draggable');
[].forEach.call(cols, function (col) {
    col.addEventListener('dragstart', handleDragStart, false);
});

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault(); // Necessary. Allows us to drop.
    }

    e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

    return false;
}

function handleDragEnter(e) {
    // this / e.target is the current hover target.
    this.classList.add('over');
}

function handleDragLeave(e) {
    this.classList.remove('over');  // this / e.target is previous target element.
}

var cols = document.querySelectorAll('.table tr.draggable');
[].forEach.call(cols, function (col) {
    col.addEventListener('dragstart', handleDragStart, false);
    col.addEventListener('dragenter', handleDragEnter, false);
    col.addEventListener('dragover', handleDragOver, false);
    col.addEventListener('dragleave', handleDragLeave, false);
    col.addEventListener('dragend', handleDragEnd, false);
    col.addEventListener('drop', handleDrop, false);
});

function handleDrop(e) {
    // this / e.target is current target element.

    if (e.stopPropagation) {
        e.stopPropagation(); // stops the browser from redirecting.
    }

    if (dragSrcEl != this) {

        currentId = dragSrcEl.id;
        moveId = this.id;

        processPosition({posx: currentId, posy: moveId});
        // Set the source column's HTML to the HTML of the column we dropped on.
        dragSrcEl.innerHTML = this.innerHTML;
        this.innerHTML = e.dataTransfer.getData('text/html');
    }

    // See the section on the DataTransfer object.

    return false;
}

function handleDragEnd(e) {
    // this/e.target is the source node.

    [].forEach.call(cols, function (col) {
        col.classList.remove('over');
        col.style.opacity = '1';
    });
}

function processPosition(params) {

    var url = $('#url').val() + 'pparam/position/' + params.posx + '/' + params.posy;
    
    $.get(url);
}