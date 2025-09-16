function $(id) {
    return document.getElementById(id);
}

async function loadAsymUnit(pdbId, fmt, map, sim) {
    var src = map.get(pdbId);
    var path = "unsimulated";
    if ( 2 == src ) {
        var path = "simulation_" + sim;
    }
    var url =
	'https://syngapmissenseserver.utu.fi/data/'
	+ path + '/' + pdbId + '.pdb';
    await BasicMolStarWrapper.load({ url: url, format: fmt });
    var doc = 'table.php?q=' + pdbId;
    $('data').src = doc;
    if (pdbId != 'WT') {
	let residue = pdbId.substring(1, 4);
	BasicMolStarWrapper.interactivity.highlightOnRes( residue );
    }
}

async function loadMembrane(fmt) {
    var url =
	'https://syngapmissenseserver.utu.fi/data/complexes/phophate_layer.pdb';
    await BasicMolStarWrapper.loadmore({ url: url, format: fmt });
}

async function loadRAS(fmt) {
    var url =
	'https://syngapmissenseserver.utu.fi/data/complexes/ras.pdb';
    await BasicMolStarWrapper.loadmore({ url: url, format: fmt });
}

async function loadWT(fmt) {
    var url =
	'https://syngapmissenseserver.utu.fi/data/complexes/wt_0.pdb';
    await BasicMolStarWrapper.loadmore({ url: url, format: fmt });
}

function addControl(label, action) {
    var btn = document.createElement('button');
    btn.onclick = action;
    btn.innerText = label;
    $('controls').appendChild(btn);
}

function addTippedControl(label, action, tiptext) {
    var btn = document.createElement('button');
    btn.classList.add('tooltip');
    btn.onclick = action;
    btn.innerText = label;
    $('controls').appendChild(btn);

    var tip = document.createElement('span');
    tip.classList.add('tooltiptext');
    tip.textContent = tiptext;
    btn.appendChild(tip);
}

function reFocus(Id, Num) {
    var res = parseInt(Num, 10);
    if ( Id != 'WT' && res != 'NaN' )
	BasicMolStarWrapper.interactivity.highlightOnRes( res );
}

function focusOn() {
    var x = prompt("Enter a residue number", "0");
    var res = parseInt(x, 10);
    if ( 0 < res )
	BasicMolStarWrapper.interactivity.highlightOnRes( res );
}

function addSeparator() {
    var hr = document.createElement('hr');
    $('controls').appendChild(hr);
}

function addHeader(header) {
    var h = document.createElement('h3');
    h.innerText = header;
    $('controls').appendChild(h);
}

function addLink(label, dest) {
    const anchor = document.createElement('a');
    anchor.href = dest;
    anchor.innerText = label;
    $('controls').appendChild(anchor);
}

function downloadFile(pdbIdx, map, sim) {
    var src = map.get(pdbId);
    var path = "unsimulated";
    var prefix = "";
    if ( 2 == src ) {
        path = "simulation_" + sim;
        prefix = 'sim' + sim + '_';
    }
    console.log
    var urlx = "";
    urlx =
	'https://syngapmissenseserver.utu.fi/data/'
	+ path + '/' + pdbIdx + '.pdb';
    if ( pdbIdx != "" ) {
	const filename = pdbIdx + '.pdb';
	fetch(urlx)
	    .then(response => response.blob())
	    .then(blob => {
		const link = document.createElement("a");
		link.href = URL.createObjectURL(blob);
		link.download = prefix + filename;
		link.click();
	    })
	    .catch(console.error);
    }
}

function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}
