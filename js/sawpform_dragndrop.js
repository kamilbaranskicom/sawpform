function baseName(str) {
	let base = new String(str).substring(str.lastIndexOf('/') + 1);

	if (base.lastIndexOf('.') != -1)
		base = base.substring(0, base.lastIndexOf('.'));
	return base;
}

// modified from http://html5demos.com/file-api
const holder = document.getElementById('posttresc'),
	state = document.getElementById('status'),
	wszystkofile = document.getElementById('wszystkofile'),
	download = document.getElementById('download'),
	generujodrazu = document.getElementById('generujodrazu'),
	formularz = document.getElementById('formularz');

if (typeof window.FileReader === 'undefined') {
	state.className = 'fail';
} else {
	state.className = 'success';
	state.innerHTML = 'File API & FileReader available. Drag&drop powinno działać, spróbuj upuścić plik txt (utf8) na textarea.';
}

holder.ondragover = function () {
	this.className = 'hover';
	return false;
};

holder.ondragend = function () {
	this.className = '';
	return false;
};

holder.ondrop = function (e) {
	this.className = '';
	e.preventDefault();

	const file = e.dataTransfer.files[0],
		reader = new FileReader();

	reader.onload = function (event) {
		console.log(event.target);
		console.log('cóż');

		holder.value = event.target.result;
		// holder.innerText = event.target.result.replace("/\r/g", "\n");
	};

	console.log(file);
	wszystkofile.value = baseName(file.name) + '.pdf';
	download.checked = true;
	reader.onloadend = function (evt) {
		// file is loaded
		if (generujodrazu.checked) {
			formularz.submit();
		};
	};
	reader.readAsText(file);
	
	return false;
};