var ImageUploader = (function () {
	function ImageUploader(c, id, key, increment, table, photo_link) {
		this._allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
		this._upload_url = '/administrator-cms/ajax/images/updatePhotoConstructor';
		this._key = key;
		this._increment = increment;
		this._table = table;
		this._container = c;
		this._path = c.querySelectorAll('#path_image')[0].value;
		this._record_id = id;
		this._record_photo_link = photo_link;
		var types = c.querySelectorAll('input[name=type_upload]');
		this.setThumbHeight = this.setThumbHeight.bind(this);
		this.rotateLeft = this.rotateLeft.bind(this);
		this.rotateRight = this.rotateRight.bind(this);
		this.resetCrop = this.resetCrop.bind(this);
		this.zoomIn = this.zoomIn.bind(this);
		this.zoomOut = this.zoomOut.bind(this);
		this.moveLeft = this.moveLeft.bind(this);
		this.moveRight = this.moveRight.bind(this);
		this.moveUp = this.moveUp.bind(this);
		this.moveDown = this.moveDown.bind(this);
		this.uploadTrigger = this.uploadTrigger.bind(this);
		this.validateUrl = this.validateUrl.bind(this);
		this.uploadImageFile = this.uploadImageFile.bind(this);
		this.createThumbs = this.createThumbs.bind(this);
		this.updateCurrentImage = this.updateCurrentImage.bind(this);
		this.XHRequest = this.XHRequest.bind(this);
		this.buildCropper = this.buildCropper.bind(this);
		this.alertText = this.alertText.bind(this);
		this.listener(types, 'change', 'setUploadType');
		this.listener(c.querySelectorAll('#width_image'), 'keyup', 'setThumbWidth');
		this.listener(c.querySelectorAll('#height_image'), 'keyup', 'setThumbHeight');
		this.listener(c.querySelectorAll('#image_file'), 'change', 'uploadImageFile');
	}
	ImageUploader.prototype.setThumbWidth = function (prop) {
		var limit = parseInt(prop.target.getAttribute('data-limit'));
		var value = parseInt(prop.target.value);
		if (value <= limit) this._thumb_width = value;
		else this.alertText('Maximum image width is ' + limit + 'px.');
	};
	ImageUploader.prototype.setThumbHeight = function (prop) {
		var limit = parseInt(prop.target.getAttribute('data-limit'));
		var value = parseInt(prop.target.value);
		if (value <= limit) this._thumb_height = value;
		else this.alertText('Maximum image height is ' + limit + 'px.');
	};
	ImageUploader.prototype.addClass = function (obj, classname) {
		for (var i = 0; i < obj.length; i++) {
			obj[i].classList.add(classname);
		}
	};
	ImageUploader.prototype.removeClass = function (obj, classname) {
		for (var i = 0; i < obj.length; i++) {
			obj[i].classList.remove(classname);
		}
	};
	ImageUploader.prototype.listener = function (element, event, callback) {
		for (var i = 0; i < element.length; i++) {
			element[i].addEventListener(event, this[callback]);
		}
	};
	ImageUploader.prototype.rotateLeft = function () {
		if (this.cropper) this.cropper.rotate(-45);
		return false;
	};
	ImageUploader.prototype.rotateRight = function () {
		if (this.cropper) this.cropper.rotate(45);
		return false;
	};
	ImageUploader.prototype.resetCrop = function () {
		if (this.cropper) this.cropper.reset();
		return false;
	};
	ImageUploader.prototype.scaleX = function () {
		(this.cropper && this.cropper.getImageData().scaleX == 1) ? this.cropper.scaleX(-1) : this.cropper.scaleX(1);
		return false;
	};
	ImageUploader.prototype.scaleY = function () {
		(this.cropper && this.cropper.getImageData().scaleY == 1) ? this.cropper.scaleY(-1) : this.cropper.scaleY(1);
		return false;
	};
	ImageUploader.prototype.zoomIn = function () {
		if (this.cropper) this.cropper.zoom(0.1);
		return false;
	};
	ImageUploader.prototype.zoomOut = function () {
		if (this.cropper) this.cropper.zoom(-0.1);
		return false;
	};
	ImageUploader.prototype.moveLeft = function () {
		if (this.cropper) this.cropper.move(-1, 0);
		return false;
	};
	ImageUploader.prototype.moveRight = function () {
		if (this.cropper) this.cropper.move(1, 0);
		return false;
	};
	ImageUploader.prototype.moveUp = function () {
		if (this.cropper) this.cropper.move(0, -1);
		return false;
	};
	ImageUploader.prototype.moveDown = function () {
		if (this.cropper) this.cropper.move(0, 1);
		return false;
	};
	ImageUploader.prototype.zoomToOrigin = function () {
		if (this.cropper) this.cropper.zoomTo(1);
		return false;
	};
	ImageUploader.prototype.cancelCrop = function () {
		if (this.cropper) this.cropper.destroy();
		return false;
	};
	ImageUploader.prototype.uploadTrigger = function () {
		this._container.querySelectorAll('#image_file')[0].click();
	};
	ImageUploader.prototype.validateUrl = function (url) {
		var extensions = this._allowed_ext.join('|\\.');
		var string = '^(https?:\/\/)?([\\dA-Za-z\.-]+)\.([a-z\.]{2,6})([\/\\w \.-]*)*(\\.' + extensions + ')\/?$';
		var regex = new RegExp(string);
		return url.match(regex);
	};
	ImageUploader.prototype.uploadImageFile = function (prop) {
		var obj = this;
		var image;
		var data = new FormData();
		data.append('type', 'file');
		data.append('path', obj._path);

		data.append('id', obj._record_id);
		data.append('photo_key', obj._key);
		data.append('increment', obj._increment);
		data.append('photo_link', obj._record_photo_link);
		data.append('table', obj._table);
		data.append('image', prop.target.files[0]);

		obj.XHRequest(data, obj.updateCurrentImage);
		prop.target.value = null;
	};
	ImageUploader.prototype.createThumbs = function () {
		if (this.cropper) {
			var reg = new RegExp('\.png\?');
			var image;
			if (this.cropper.originalUrl.match(reg)) {
				var canvas = this.cropper.getCroppedCanvas();
				this.resample(canvas, this._thumb_width, this._thumb_height, true);
				image = canvas.toDataURL();
			}
			else {
				this.resample(canvas, this._thumb_width, this._thumb_height, true);
				image = canvas.toDataURL('image/jpeg');
			}

			var data = 'type=' + encodeURIComponent('file') + '&image=' + encodeURIComponent(image) + '&path=' + this._path + '&upload_type=' + encodeURIComponent(this._upload_type) + '&thumb_width=' + encodeURIComponent(this._thumb_width) + '&thumb_height=' + encodeURIComponent(this._thumb_height) + '&id=' + encodeURIComponent(this._record_id) + '&table=' + encodeURIComponent(this._table);
			this.XHRequest(data, this.updateCurrentImage, this._save_cropped_image_url);
			this.cropper.destroy();
		}
	};
	ImageUploader.prototype.updateCurrentImage = function (response) {
		if (this.cropper) this.cropper.destroy();
		if (response.image) {
			this._container.querySelector('.show-image').classList.add("active");
			this._container.querySelector('#record_image').setAttribute('src', '/' + response.image);
			document.querySelector('.photo-edit-large').setAttribute('src', '/' + response.image);
			this._container.querySelector('#current_photo').value = response.image.split('?')[0];
			this.alertText(response.status);
		}
		else this.alertText(response.error);
	};
	ImageUploader.prototype.XHRequest = function (body, callback, url) {
		if (url === void 0) url = this._upload_url;
		var obj = this;
		var xhr = new XMLHttpRequest();
		if (!xhr) {
			this.alertText("Browser does not support XHR request correctly. Please, update your browser to normal version.");
			return false;
		}
		xhr.upload.onprogress = function(event) {
			var percent = 100 / (event.total / event.loaded);
			obj.alertText('Загрузка изображения - ' + percent.toFixed(2) + '%');
			console.log('File upload progress: ', percent + '% - ' + event.loaded + ' / ' + event.total);
		};
		xhr.timeout = 120000;
		xhr.onloadstart = function () {
		};
		xhr.ontimeout = function () {
			obj.alertText('Ошибка, сервер не отвечает на запрос.');
		};
		xhr.onload = xhr.onerror = function () {
			if (xhr.status != 200) obj.alertText(xhr.status + ': ' + xhr.statusText);
			else if (xhr.status == 200 && xhr.readyState == 4) callback(JSON.parse(xhr.response));
			else obj.alertText(xhr.responseText);
		};
		xhr.open('POST', url, true);
		if(typeof(body) != 'object') {
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.setRequestHeader('Content-Encoding', 'gzip');
		}
		xhr.send(body);
	};
	ImageUploader.prototype.buildCropper = function (data) {
		if (this.cropper) this.cropper.destroy();
		this._container.querySelectorAll('#new_image').item(0).setAttribute('src', '/' + data.image);
		var Cropper = window.Cropper;
		var container = this._container.querySelector('.img-container');
		var image = container.getElementsByTagName('img').item(0);
		var options = {
			viewMode: 0,
			aspectRatio: this._thumb_width / this._thumb_height,
			preview: '.img-preview',
		};
		this.cropper = new Cropper(image, options);
	};
	ImageUploader.prototype.alertText = function (text, type, time) {
		if (type === void 0) type = "error";
		if (time === void 0) time = 5000;

		var alert_container = this._container.querySelectorAll('#alert_text');
		if (text != '') {
			alert_container[0].innerHTML = text;
			alert_container[0].classList.add(type);
			setTimeout(function () {
				alert_container[0].innerHTML = '';
				alert_container[0].classList.remove(type);
			}, time);
		}
	};
	ImageUploader.prototype.resample = function (canvas, width, height, resize_canvas) {
		var width_source = canvas.width;
		var height_source = canvas.height;
		width = Math.round(width);
		height = Math.round(height);

		var ratio_w = width_source / width;
		var ratio_h = height_source / height;
		var ratio_w_half = Math.ceil(ratio_w / 2);
		var ratio_h_half = Math.ceil(ratio_h / 2);

		var ctx = canvas.getContext("2d");
		var img = ctx.getImageData(0, 0, width_source, height_source);
		var img2 = ctx.createImageData(width, height);
		var data = img.data;
		var data2 = img2.data;

		for (var j = 0; j < height; j++) {
			for (var i = 0; i < width; i++) {
				var x2 = (i + j * width) * 4;
				var weight = 0;
				var weights = 0;
				var weights_alpha = 0;
				var gx_r = 0;
				var gx_g = 0;
				var gx_b = 0;
				var gx_a = 0;
				var center_y = (j + 0.5) * ratio_h;
				var yy_start = Math.floor(j * ratio_h);
				var yy_stop = Math.ceil((j + 1) * ratio_h);
				for (var yy = yy_start; yy < yy_stop; yy++) {
					var dy = Math.abs(center_y - (yy + 0.5)) / ratio_h_half;
					var center_x = (i + 0.5) * ratio_w;
					var w0 = dy * dy; //pre-calc part of w
					var xx_start = Math.floor(i * ratio_w);
					var xx_stop = Math.ceil((i + 1) * ratio_w);
					for (var xx = xx_start; xx < xx_stop; xx++) {
						var dx = Math.abs(center_x - (xx + 0.5)) / ratio_w_half;
						var w = Math.sqrt(w0 + dx * dx);
						if (w >= 1) {
							//pixel too far
							continue;
						}
						//hermite filter
						weight = 2 * w * w * w - 3 * w * w + 1;
						var pos_x = 4 * (xx + yy * width_source);
						//alpha
						gx_a += weight * data[pos_x + 3];
						weights_alpha += weight;
						//colors
						if (data[pos_x + 3] < 255) weight = weight * data[pos_x + 3] / 250;
						gx_r += weight * data[pos_x];
						gx_g += weight * data[pos_x + 1];
						gx_b += weight * data[pos_x + 2];
						weights += weight;
					}
				}
				data2[x2] = gx_r / weights;
				data2[x2 + 1] = gx_g / weights;
				data2[x2 + 2] = gx_b / weights;
				data2[x2 + 3] = gx_a / weights_alpha;
			}
		}
		//clear and resize canvas
		if (resize_canvas === true) {
			canvas.width = width;
			canvas.height = height;
		} else ctx.clearRect(0, 0, width_source, height_source);

		//draw
		ctx.putImageData(img2, 0, 0);
	};
	return ImageUploader;
})();