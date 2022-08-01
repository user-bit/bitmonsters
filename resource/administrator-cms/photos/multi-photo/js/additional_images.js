var MultiImageUploader = (function () {
	function MultiImageUploader(c, ext, id, table) {
		this._allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
		this._upload_url = '/administrator-cms/ajax/images/updateMultiPhoto';
		this._table = table;
		this._container = c;
		this._path = c.querySelectorAll('#path')[0].value;
		this._record_id = id;
		this._allowed_ext = ext;
		this.selectFiles = this.selectFiles.bind(this);
		this.uploadImageFile = this.uploadImageFile.bind(this);
		this.XHRequest = this.XHRequest.bind(this);
		this.dragDrop = this.dragDrop.bind(this);
		this.processFiles = this.processFiles.bind(this);
		this.updateTable = this.updateTable.bind(this);
		this.lister(c.querySelectorAll('#extra_files'), 'change', 'selectFiles');
	}

	MultiImageUploader.prototype.lister = function (element, event, callback) {
		for (var i = 0; i < element.length; i++) {
			element[i].addEventListener(event, this[callback]);
		}
	};
	MultiImageUploader.prototype.dragEnter = function (prop) {
		prop.target.classList.add('on');
	};
	MultiImageUploader.prototype.dragLeave = function (prop) {
		prop.target.classList.remove('on');
	};
	MultiImageUploader.prototype.dragOver = function (prop) {
		prop.preventDefault();
	};
	MultiImageUploader.prototype.dragDrop = function (prop) {
		prop.preventDefault();
		var dt = prop.dataTransfer;
		this.selectFiles(dt);
		return false;
	};
	MultiImageUploader.prototype.selectFiles = function (prop) {
		var fl = prop.files;
		if (!fl)
			fl = prop.target.files;
		var list = '';
		var i = 1;
		for (var prop in fl) {
			if (fl.hasOwnProperty(prop)) list += "<span>" + i + ". " + fl[prop].name + "</span>";
			i++;
		}
		this.processFiles(fl);
	};
	MultiImageUploader.prototype.processFiles = function (files) {
		for (var prop in files) {
			if (files.hasOwnProperty(prop)) this.uploadImageFile(files[prop]);
		}
	};
	MultiImageUploader.prototype.uploadImageFile = function (file) {
		var obj = this;
		var image;
		var data = new FormData();
		data.append('type', 'file');
		data.append('path', obj._path);
		data.append('name', file.name);
		data.append('upload_type', obj._upload_type);
		data.append('id', obj._record_id);
		data.append('table', obj._table);
		data.append('image', file);

		obj.XHRequest(data, obj.updateTable);

		return true;

	};
	MultiImageUploader.prototype.updateTable = function (response) {
		this._container.querySelectorAll('#tb_extra_photo')[0].innerHTML = response.content;
	};
	MultiImageUploader.prototype.XHRequest = function (body, callback, url) {
		if (url === void 0) url = this._upload_url;
		var xhr = new XMLHttpRequest();
		if (!xhr) return false;
		xhr.timeout = 120000;
		xhr.upload.onprogress = function(event) {
			var percent = 100 / (event.total / event.loaded);
			console.log('File upload progress: ', percent + '% - ' + event.loaded + ' / ' + event.total);
		};
		xhr.onloadstart = function () {
		};
		xhr.ontimeout = function () {
		};
		xhr.onload = xhr.onerror = function () {
			if (xhr.status == 200 && xhr.readyState == 4) callback(JSON.parse(xhr.response));
		};
		xhr.open('POST', url, true);
		if(typeof(body) != 'object') xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(body);
	};
	return MultiImageUploader;
})();
//# sourceMappingURL=additional_images.js.map