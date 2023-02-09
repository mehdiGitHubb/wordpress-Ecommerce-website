let ThemifyImageResize;
((Themify, doc, und) => {
    /* 
WebGLImageFilter - MIT Licensed
2013, Dominic Szablewski - phoboslab.org
*/

(function(window){

const WebGLProgram = function( gl, vertexSource, fragmentSource ) {

	var _collect = function( source, prefix, collection ) {
		var r = new RegExp('\\b' + prefix + ' \\w+ (\\w+)', 'ig');
		source.replace(r, function(match, name) {
			collection[name] = 0;
			return match;
		});
	};

	var _compile = function( gl, source, type ) {
		var shader = gl.createShader(type);
		gl.shaderSource(shader, source);
		gl.compileShader(shader);

		if( !gl.getShaderParameter(shader, gl.COMPILE_STATUS) ) {
			console.log(gl.getShaderInfoLog(shader));
			return null;
		}
		return shader;
	};


	this.uniform = {};
	this.attribute = {};

	var _vsh = _compile(gl, vertexSource, gl.VERTEX_SHADER);
	var _fsh = _compile(gl, fragmentSource, gl.FRAGMENT_SHADER);

	this.id = gl.createProgram();
	gl.attachShader(this.id, _vsh);
	gl.attachShader(this.id, _fsh);
	gl.linkProgram(this.id);

	if( !gl.getProgramParameter(this.id, gl.LINK_STATUS) ) {
		console.log(gl.getProgramInfoLog(this.id));
	}

	gl.useProgram(this.id);

	// Collect attributes
	_collect(vertexSource, 'attribute', this.attribute);
	for( var a in this.attribute ) {
		this.attribute[a] = gl.getAttribLocation(this.id, a);
	}

	// Collect uniforms
	_collect(vertexSource, 'uniform', this.uniform);
	_collect(fragmentSource, 'uniform', this.uniform);
	for( var u in this.uniform ) {
		this.uniform[u] = gl.getUniformLocation(this.id, u);
	}
},
WebGLImageFilter = window.WebGLImageFilter = function (params) {
	if (!params)
		params = { };

	var gl = null,
		_drawCount = 0,
		_sourceTexture = null,
		_lastInChain = false,
		_currentFramebufferIndex = -1,
		_tempFramebuffers = [null, null],
		_filterChain = new Map(),
		_width = -1, 
		_height = -1,
		_vertexBuffer = null,
		_currentProgram = null,
		_canvas = params.canvas || document.createElement('canvas'),
        // key is the shader program source, value is the compiled program
        _shaderProgramCache = {},
        gl = _canvas.getContext("webgl") || _canvas.getContext("experimental-webgl");
	if( !gl ) {
		throw "Couldn't get WebGL context";
	}

	
	this.addFilter = function( name,...args ) {
        _filterChain.set(_filter[name],args);
	};

	this.reset = function() {
		_filterChain.clear();
	};

	var applied = false;
	
	this.apply = function( image ) {
		_resize( image.width, image.height );
		_drawCount = 0;
        const size=_filterChain.size;
		// Create the texture for the input image if we haven't yet
		if (!_sourceTexture)
			_sourceTexture = gl.createTexture();

		gl.bindTexture(gl.TEXTURE_2D, _sourceTexture);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST); 
		if (!applied) {
			gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
			applied = true;
		} else {
			gl.texSubImage2D(gl.TEXTURE_2D, 0, 0, 0, gl.RGBA, gl.UNSIGNED_BYTE, image);
		}

		// No filters? Just draw
		if( size === 0 ) {
			_compileShader(SHADER.FRAGMENT_IDENTITY);
			_draw();
			return _canvas;
		}
        let i=0;
        for(let [f,args] of _filterChain){
            _lastInChain = (i === size-1);
			f.apply(this,args || []);
            ++i;
        }

		return _canvas;
	};

	var _resize = function( width, height ) {
		// Same width/height? Nothing to do here
		if( width === _width && height === _height ) { return; }


		_canvas.width = _width = width;
		_canvas.height = _height = height;

		// Create the context if we don't have it yet
		if( !_vertexBuffer ) {
			// Create the vertex buffer for the two triangles [x, y, u, v] * 6
			var vertices = new Float32Array([
				-1, -1, 0, 1,  1, -1, 1, 1,  -1, 1, 0, 0,
				-1, 1, 0, 0,  1, -1, 1, 1,  1, 1, 1, 0
			]);
			_vertexBuffer = gl.createBuffer(),
			gl.bindBuffer(gl.ARRAY_BUFFER, _vertexBuffer);
			gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);

			// Note sure if this is a good idea; at least it makes texture loading
			// in Ejecta instant.
			gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, true);
		}

		gl.viewport(0, 0, _width, _height);

		// Delete old temp framebuffers
		_tempFramebuffers = [null, null];
	};

	var _getTempFramebuffer = function( index ) {
		_tempFramebuffers[index] = 
			_tempFramebuffers[index] || 
			_createFramebufferTexture( _width, _height );

		return _tempFramebuffers[index];
	};

	var _createFramebufferTexture = function( width, height ) {
		var fbo = gl.createFramebuffer();
		gl.bindFramebuffer(gl.FRAMEBUFFER, fbo);

		var renderbuffer = gl.createRenderbuffer();
		gl.bindRenderbuffer(gl.RENDERBUFFER, renderbuffer);

		var texture = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, texture);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, width, height, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);

		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);

		gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, texture, 0);

		gl.bindTexture(gl.TEXTURE_2D, null);
		gl.bindFramebuffer(gl.FRAMEBUFFER, null);

		return {fbo: fbo, texture: texture};
	};

	var _draw = function( flags ) {
		var source = null, 
			target = null,
			flipY = false;

		// Set up the source
		if( _drawCount === 0 ) {
			// First draw call - use the source texture
			source = _sourceTexture;
		}
		else {
			// All following draw calls use the temp buffer last drawn to
			source =  _getTempFramebuffer(_currentFramebufferIndex).texture;
		}
		_drawCount++;


		// Set up the target
		if( _lastInChain && !(flags & DRAW.INTERMEDIATE) ) {
			// Last filter in our chain - draw directly to the WebGL Canvas. We may
			// also have to flip the image vertically now
			target = null;
			flipY = _drawCount % 2 === 0;
		}
		else {
			// Intermediate draw call - get a temp buffer to draw to
			_currentFramebufferIndex = (_currentFramebufferIndex+1) % 2;
			target = _getTempFramebuffer(_currentFramebufferIndex).fbo;
		}

		// Bind the source and target and draw the two triangles
		gl.bindTexture(gl.TEXTURE_2D, source);
		gl.bindFramebuffer(gl.FRAMEBUFFER, target);

		gl.uniform1f(_currentProgram.uniform.flipY, (flipY ? -1 : 1) );
		gl.drawArrays(gl.TRIANGLES, 0, 6);
	};

	var _compileShader = function( fragmentSource ) {
		if (_shaderProgramCache[fragmentSource]) {
			_currentProgram = _shaderProgramCache[fragmentSource];
			gl.useProgram(_currentProgram.id);
			return _currentProgram;
		}

		// Compile shaders
		_currentProgram = new WebGLProgram( gl, SHADER.VERTEX_IDENTITY, fragmentSource );

		var floatSize = Float32Array.BYTES_PER_ELEMENT;
		var vertSize = 4 * floatSize;
		gl.enableVertexAttribArray(_currentProgram.attribute.pos);
		gl.vertexAttribPointer(_currentProgram.attribute.pos, 2, gl.FLOAT, false, vertSize , 0 * floatSize);
		gl.enableVertexAttribArray(_currentProgram.attribute.uv);
		gl.vertexAttribPointer(_currentProgram.attribute.uv, 2, gl.FLOAT, false, vertSize, 2 * floatSize);

		_shaderProgramCache[fragmentSource] = _currentProgram;
		return _currentProgram;
	};


	var DRAW = { INTERMEDIATE: 1 };

	var SHADER = {};
	SHADER.VERTEX_IDENTITY = [
		'precision highp float;',
		'attribute vec2 pos;',
		'attribute vec2 uv;',
		'varying vec2 vUv;',
		'uniform float flipY;',

		'void main(void) {',
			'vUv = uv;',
			'gl_Position = vec4(pos.x, pos.y*flipY, 0.0, 1.);',
		'}'
	].join('\n');

	SHADER.FRAGMENT_IDENTITY = [
		'precision highp float;',
		'varying vec2 vUv;',
		'uniform sampler2D texture;',

		'void main(void) {',
			'gl_FragColor = texture2D(texture, vUv);',
		'}'
	].join('\n');


	var _filter = {};



	// -------------------------------------------------------------------------
	// Color Matrix Filter

	_filter.colorMatrix = function( matrix ) {
		// Create a Float32 Array and normalize the offset component to 0-1
		var m = new Float32Array(matrix);
		m[4] /= 255;
		m[9] /= 255;
		m[14] /= 255;
		m[19] /= 255;

		// Can we ignore the alpha value? Makes things a bit faster.
		var shader = (1===m[18]&&0===m[3]&&0===m[8]&&0===m[13]&&0===m[15]&&0===m[16]&&0===m[17]&&0===m[19])
			? _filter.colorMatrix.SHADER.WITHOUT_ALPHA
			: _filter.colorMatrix.SHADER.WITH_ALPHA;
		
		gl.uniform1fv(_compileShader(shader).uniform.m, m);
		_draw();
	};

	_filter.colorMatrix.SHADER = {};
	_filter.colorMatrix.SHADER.WITH_ALPHA = [
		'precision highp float;',
		'varying vec2 vUv;',
		'uniform sampler2D texture;',
		'uniform float m[20];',

		'void main(void) {',
			'vec4 c = texture2D(texture, vUv);',
			'gl_FragColor.r = m[0] * c.r + m[1] * c.g + m[2] * c.b + m[3] * c.a + m[4];',
			'gl_FragColor.g = m[5] * c.r + m[6] * c.g + m[7] * c.b + m[8] * c.a + m[9];',
			'gl_FragColor.b = m[10] * c.r + m[11] * c.g + m[12] * c.b + m[13] * c.a + m[14];',
			'gl_FragColor.a = m[15] * c.r + m[16] * c.g + m[17] * c.b + m[18] * c.a + m[19];',
		'}'
	].join('\n');
	_filter.colorMatrix.SHADER.WITHOUT_ALPHA = [
		'precision highp float;',
		'varying vec2 vUv;',
		'uniform sampler2D texture;',
		'uniform float m[20];',

		'void main(void) {',
			'vec4 c = texture2D(texture, vUv);',
			'gl_FragColor.r = m[0] * c.r + m[1] * c.g + m[2] * c.b + m[4];',
			'gl_FragColor.g = m[5] * c.r + m[6] * c.g + m[7] * c.b + m[9];',
			'gl_FragColor.b = m[10] * c.r + m[11] * c.g + m[12] * c.b + m[14];',
			'gl_FragColor.a = c.a;',
		'}'
	].join('\n');

	_filter.brightness = function( brightness ) {
		var b = (brightness || 0) + 1;
		_filter.colorMatrix([
				b, 0, 0, 0, 0,
				0, b, 0, 0, 0,
				0, 0, b, 0, 0,
				0, 0, 0, 1, 0
		]);
	};

	_filter.saturation = function( amount ) {
		var x = (amount || 0) * 2/3 + 1;
		var y = ((x-1) *-0.5);
		_filter.colorMatrix([
			x, y, y, 0, 0,
			y, x, y, 0, 0,
			y, y, x, 0, 0,
			0, 0, 0, 1, 0
		]);
	};

	_filter.desaturate = function() {
		_filter.saturation(-1);
	};

	_filter.contrast = function( amount ) {
		var v = (amount || 0) + 1;
		var o = -128 * (v-1);
		
		_filter.colorMatrix([
			v, 0, 0, 0, o,
			0, v, 0, 0, o,
			0, 0, v, 0, o,
			0, 0, 0, 1, 0
		]);
	};

	_filter.negative = function() {
		_filter.contrast(-2);
	};

	_filter.hue = function( rotation ) {
		rotation = (rotation || 0)/180 * Math.PI;
		var cos = Math.cos(rotation),
			sin = Math.sin(rotation),
			lumR = 0.213,
			lumG = 0.715,
			lumB = 0.072;

		_filter.colorMatrix([
			lumR+cos*(1-lumR)+sin*(-lumR),lumG+cos*(-lumG)+sin*(-lumG),lumB+cos*(-lumB)+sin*(1-lumB),0,0,
			lumR+cos*(-lumR)+sin*(0.143),lumG+cos*(1-lumG)+sin*(0.140),lumB+cos*(-lumB)+sin*(-0.283),0,0,
			lumR+cos*(-lumR)+sin*(-(1-lumR)),lumG+cos*(-lumG)+sin*(lumG),lumB+cos*(1-lumB)+sin*(lumB),0,0,
			0, 0, 0, 1, 0
		]);
	};

	_filter.desaturateLuminance = function() {
		_filter.colorMatrix([
			0.2764723, 0.9297080, 0.0938197, 0, -37.1,
			0.2764723, 0.9297080, 0.0938197, 0, -37.1,
			0.2764723, 0.9297080, 0.0938197, 0, -37.1,
			0, 0, 0, 1, 0
		]);
	};

	_filter.sepia = function(amount) {
		_filter.colorMatrix([
			(1 - (.607 * amount)), .7689999*amount, .18899999*amount, 0, 0,
			.349*amount, (1 - (.314 * amount)), .16799999*amount, 0, 0,
			.272*amount, .5339999*amount, (1 - (.869 * amount)), 0, 0,
			0,0,0,1,0
		]);
	};

	_filter.brownie = function() {
		_filter.colorMatrix([
			0.5997023498159715,0.34553243048391263,-0.2708298674538042,0,47.43192855600873,
			-0.037703249837783157,0.8609577587992641,0.15059552388459913,0,-36.96841498319127,
			0.24113635128153335,-0.07441037908422492,0.44972182064877153,0,-7.562075277591283,
			0,0,0,1,0
		]);
	};

	_filter.vintagePinhole = function() {
		_filter.colorMatrix([
			0.6279345635605994,0.3202183420819367,-0.03965408211312453,0,9.651285835294123,
			0.02578397704808868,0.6441188644374771,0.03259127616149294,0,7.462829176470591,
			0.0466055556782719,-0.0851232987247891,0.5241648018700465,0,5.159190588235296,
			0,0,0,1,0
		]);
	};

	_filter.kodachrome = function() {
		_filter.colorMatrix([
			1.1285582396593525,-0.3967382283601348,-0.03992559172921793,0,63.72958762196502,
			-0.16404339962244616,1.0835251566291304,-0.05498805115633132,0,24.732407896706203,
			-0.16786010706155763,-0.5603416277695248,1.6014850761964943,0,35.62982807460946,
			0,0,0,1,0
		]);
	};

	_filter.technicolor = function() {
		_filter.colorMatrix([
			1.9125277891456083,-0.8545344976951645,-0.09155508482755585,0,11.793603434377337,
			-0.3087833385928097,1.7658908555458428,-0.10601743074722245,0,-70.35205161461398,
			-0.231103377548616,-0.7501899197440212,1.847597816108189,0,30.950940869491138,
			0,0,0,1,0
		]);
	};

	_filter.polaroid = function() {
		_filter.colorMatrix([
			1.438,-0.062,-0.062,0,0,
			-0.122,1.378,-0.122,0,0,
			-0.016,-0.016,1.483,0,0,
			0,0,0,1,0
		]);
	};

	_filter.shiftToBGR = function() {
		_filter.colorMatrix([
			0,0,1,0,0,
			0,1,0,0,0,
			1,0,0,0,0,
			0,0,0,1,0
		]);
	};


	// -------------------------------------------------------------------------
	// Convolution Filter

	_filter.convolution = function( matrix ) {
		var m = new Float32Array(matrix);
		var pixelSizeX = 1 / _width;
		var pixelSizeY = 1 / _height;

		var program = _compileShader(_filter.convolution.SHADER);
		gl.uniform1fv(program.uniform.m, m);
		gl.uniform2f(program.uniform.px, pixelSizeX, pixelSizeY);
		_draw();
	};

	_filter.convolution.SHADER = [
		'precision highp float;',
		'varying vec2 vUv;',
		'uniform sampler2D texture;',
		'uniform vec2 px;',
		'uniform float m[9];',

		'void main(void) {',
			'vec4 c11 = texture2D(texture, vUv - px);', // top left
			'vec4 c12 = texture2D(texture, vec2(vUv.x, vUv.y - px.y));', // top center
			'vec4 c13 = texture2D(texture, vec2(vUv.x + px.x, vUv.y - px.y));', // top right

			'vec4 c21 = texture2D(texture, vec2(vUv.x - px.x, vUv.y) );', // mid left
			'vec4 c22 = texture2D(texture, vUv);', // mid center
			'vec4 c23 = texture2D(texture, vec2(vUv.x + px.x, vUv.y) );', // mid right

			'vec4 c31 = texture2D(texture, vec2(vUv.x - px.x, vUv.y + px.y) );', // bottom left
			'vec4 c32 = texture2D(texture, vec2(vUv.x, vUv.y + px.y) );', // bottom center
			'vec4 c33 = texture2D(texture, vUv + px );', // bottom right

			'gl_FragColor = ',
				'c11 * m[0] + c12 * m[1] + c22 * m[2] +',
				'c21 * m[3] + c22 * m[4] + c23 * m[5] +',
				'c31 * m[6] + c32 * m[7] + c33 * m[8];',
			'gl_FragColor.a = c22.a;',
		'}'
	].join('\n');


	_filter.detectEdges = function() {
		_filter.convolution.call(this, [
			0, 1, 0,
			1, -4, 1,
			0, 1, 0
		]);
	};

	_filter.sobelX = function() {
		_filter.convolution.call(this, [
			-1, 0, 1,
			-2, 0, 2,
			-1, 0, 1
		]);
	};

	_filter.sobelY = function() {
		_filter.convolution.call(this, [
			-1, -2, -1,
			 0,  0,  0,
			 1,  2,  1
		]);
	};

	_filter.sharpen = function( amount ) {
		var a = amount || 1;
		_filter.convolution.call(this, [
			0, -1*a, 0,
			-1*a, 1 + 4*a, -1*a,
			0, -1*a, 0
		]);
	};

	_filter.emboss = function( size ) {
		var s = size || 1;
		_filter.convolution.call(this, [
			-2*s, -1*s, 0,
			-1*s, 1, 1*s,
			0, 1*s, 2*s
		]);
	};


	// -------------------------------------------------------------------------
	// Blur Filter

	_filter.blur = function( size ) {
		var blurSizeX = (size/7) / _width;
		var blurSizeY = (size/7) / _height;

		var program = _compileShader(_filter.blur.SHADER);

		// Vertical
		gl.uniform2f(program.uniform.px, 0, blurSizeY);
		_draw(DRAW.INTERMEDIATE);

		// Horizontal
		gl.uniform2f(program.uniform.px, blurSizeX, 0);
		_draw();
	};

	_filter.blur.SHADER = [
		'precision highp float;',
		'varying vec2 vUv;',
		'uniform sampler2D texture;',
		'uniform vec2 px;',

		'void main(void) {',
			'gl_FragColor = vec4(0.0);',
			'gl_FragColor += texture2D(texture, vUv + vec2(-7.0*px.x, -7.0*px.y))*0.0044299121055113265;',
			'gl_FragColor += texture2D(texture, vUv + vec2(-6.0*px.x, -6.0*px.y))*0.00895781211794;',
			'gl_FragColor += texture2D(texture, vUv + vec2(-5.0*px.x, -5.0*px.y))*0.0215963866053;',
			'gl_FragColor += texture2D(texture, vUv + vec2(-4.0*px.x, -4.0*px.y))*0.0443683338718;',
			'gl_FragColor += texture2D(texture, vUv + vec2(-3.0*px.x, -3.0*px.y))*0.0776744219933;',
			'gl_FragColor += texture2D(texture, vUv + vec2(-2.0*px.x, -2.0*px.y))*0.115876621105;',
			'gl_FragColor += texture2D(texture, vUv + vec2(-1.0*px.x, -1.0*px.y))*0.147308056121;',
			'gl_FragColor += texture2D(texture, vUv                             )*0.159576912161;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 1.0*px.x,  1.0*px.y))*0.147308056121;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 2.0*px.x,  2.0*px.y))*0.115876621105;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 3.0*px.x,  3.0*px.y))*0.0776744219933;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 4.0*px.x,  4.0*px.y))*0.0443683338718;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 5.0*px.x,  5.0*px.y))*0.0215963866053;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 6.0*px.x,  6.0*px.y))*0.00895781211794;',
			'gl_FragColor += texture2D(texture, vUv + vec2( 7.0*px.x,  7.0*px.y))*0.0044299121055113265;',
		'}'
	].join('\n');


	// -------------------------------------------------------------------------
	// Pixelate Filter

	_filter.pixelate = function( size ) {
		var blurSizeX = (size) / _width;
		var blurSizeY = (size) / _height;

		var program = _compileShader(_filter.pixelate.SHADER);

		// Horizontal
		gl.uniform2f(program.uniform.size, blurSizeX, blurSizeY);
		_draw();
	};

	_filter.pixelate.SHADER = [
		'precision highp float;',
		'varying vec2 vUv;',
		'uniform vec2 size;',
		'uniform sampler2D texture;',

		'vec2 pixelate(vec2 coord, vec2 size) {',
			'return floor( coord / size ) * size;',
		'}',

		'void main(void) {',
			'gl_FragColor = vec4(0.0);',
			'vec2 coord = pixelate(vUv, size);',
			'gl_FragColor += texture2D(texture, coord);',
		'}'
	].join('\n');
};

})(window);
    'use strict';
    const webp = doc.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') !== -1,
            objects = new Map();
    ThemifyImageResize = class {
        constructor(img, q, currentSrc,isLocal) {
            this.img = img;
            const url = !currentSrc && img.dataset.orig ? img.dataset.orig : (img.currentSrc || img.src || ''),
                localUrl=Themify.upload_url || Themify.urlHost;
            this.isLocal =isLocal===true || (url.indexOf(localUrl) !== -1 || url.indexOf('blob:') !== -1 || url.indexOf('data:')!==-1) && !img.closest('[data-hasEditor]');
            if (this.isLocal === true) {
                if (url.indexOf('blob:') === -1 && url.indexOf('data:') === -1) {
                    img.dataset.orig = url;
                }
                if (img.complete) {
                    this.w = img.naturalWidth;
                    this.h = img.naturalHeight;
                    if (this.w > 2560 || this.h > 2560) {
                        this.url = url;
                        this.isBig = true;
                        this.prms = Promise.resolve();
                        return;
                    }
                }
                this.ext = url.slice((url.lastIndexOf('.') - 1 >>> 0) + 2);
                if (this.ext === 'svg') {
                    this.url = url;
                    this.prms = Promise.resolve();
                    return;
                }
                if (this.ext === 'jpg') {
                    this.ext = 'jpeg';
                }
                this.ext = 'image/' + this.ext;
                this.webp = webp === true ? 'image/webp' : this.ext;
                this.prms = new Promise(async(resolve, reject) => {

                    this.tmpImg = new Image();
                    this.tmpImg.crossOrigin = 'anonymous';
                    this.tmpImg.decoding = 'async';
                    this.tmpImg.src = url;
                    try{
                        await this.tmpImg.decode();
                        this.w = Math.trunc(this.tmpImg.naturalWidth);
                        this.h = Math.trunc(this.tmpImg.naturalHeight);
                        if (this.w > 2560 || this.h > 2560) {
                            this.url = url;
                            this.isBig = true;
                            resolve();
                            return;
                        }
                        this.aspectRatio = parseFloat(this.w / this.h);
                        this.canvas = doc.createElement('canvas');  
                        this.ctx = this.canvas.getContext('2d', {alpha: this.ext.indexOf('jpeg') === -1});
                        if (q > 0) {
                            this.q = q;
                        } else {
                            this.q = .01;
                            if (this.w < 380 && this.h < 380) {
                                this.q = 1;
                            } else if (this.w < 400 && this.h < 400) {
                                this.q = .9;
                            } else if (this.w < 450 && this.h < 450) {
                                this.q = .8;
                            } else if (this.w < 500 && this.h < 500) {
                                this.q = .7;
                            } else if (this.w < 550 && this.h < 550) {
                                this.q = .6;
                            } else if (this.w < 650 && this.h < 650) {
                                this.q = .5;
                            } else if (this.w < 850 && this.h < 850) {
                                this.q = .4;
                            } else if (this.w < 1000 && this.h < 1000) {
                                this.q = .3;
                            } else if (this.w < 1100 && this.h < 1100) {
                                this.q = .2;
                            } else if (this.w < 1200 && this.h < 1200) {
                                this.q = .1;
                            } else if (this.w > 1700 && this.h > 1700) {
                                this.q = .001;
                            }
                        }
                        this.ctx.imageSmoothingEnabled = this.q > .7;
                        this.ctx.imageSmoothingQuality = this.q > .7 ? 'high' : (this.q > .5 ? 'medium' : 'low');

                        if (this.q !== 1) {
                            this.origImg = this.tmpImg.cloneNode(true);
                        }
                        await this._applyEfects();
                        resolve();
                    }
                    catch(e){
                        reject(e);
                    }
                });
            } else {
                this.url = url;
                this.prms = Promise.resolve();
            }
        }
        lowQuality(w, h) {
            return new Promise((resolve, reject) => {
                this.prms.then(() => {
                    if (this.isLocal === false || this.isBig === true || this.ext === 'svg') {
                        resolve();
                        return;
                    }
                    const memory = navigator.deviceMemory;
                    if (!w) {
                        w = Themify.device === 'mobile' ? 400 : (memory > 4 ? 800 : 600);
                    }
                    if (!h) {
                        h = Themify.device === 'mobile' ? 400 : (memory > 4 ? 800 : 600);
                    }
                    const MAX_WIDTH = w,
                            MAX_HEIGHT = h;
                    if (this.w > MAX_WIDTH || this.h > MAX_HEIGHT) {
                        if (this.w > this.h) {
                            if (this.w > MAX_WIDTH) {
                                this.h *= (MAX_WIDTH / this.w);
                                this.w = MAX_WIDTH;
                            }
                        } else if (this.h > MAX_HEIGHT) {
                            this.w *= (MAX_HEIGHT / this.h);
                            this.h = MAX_HEIGHT;
                        }
                        this.canvas.width = this.w;
                        this.canvas.height = this.h;
                        this.aspectRatio = parseFloat(this.w / this.h);
                        this.ctx.imageSmoothingEnabled = false;
                        this.ctx.imageSmoothingQuality = 'low';

                        const bitMap = () => {
                            if (typeof createImageBitmap === 'function') {
                                return createImageBitmap(this.tmpImg, {
                                    resizeQuality: 'pixelated',
                                    resizeWidth: this.w,
                                    resizeHeight: this.h
                                });
                            }
                            return Promise.reject();
                        };
                        bitMap().then(blob => {
                            this.ctx.drawImage(blob, 0, 0);
                        })
                                .catch(() => {
                                    this.ctx.drawImage(this.tmpImg, 0, 0, this.w, this.h);
                                })
                                .then(() => {
                                    this.canvas.toBlob(blob => {
                                        try {
                                            const url = URL.createObjectURL(blob, this.webp);
                                            this.tmpImg.tfOn('load', () => {
                                                URL.revokeObjectURL(url);
                                                resolve();
                                            }, {passive: true, once: true});
                                            this.tmpImg.src = url;
                                        } catch (e) {
                                            reject(e);
                                        }
                                    }, this.webp, .1);
                                })
                                .catch(reject);
                    } else {
                        resolve();
                    }
                }).catch(reject);
            });
        }
        highQuality(w, h,ext) {
            return new Promise(async(resolve, reject) => {
                try {
                    await this.prms;
                    if (this.isLocal === false || this.isBig === true || this.ext === 'svg') {
                        resolve();
                        return;
                    }
                    if (this.origImg) {
                        await this.origImg.decode();
                        this.tmpImg = this.origImg;
                        this.w = this.tmpImg.naturalWidth;
                        this.h = this.tmpImg.naturalHeight;
                        this.aspectRatio = parseFloat(this.w / this.h);
                    }
                    const url = await this.toBlob(w, h, 1,ext);
                    resolve(url);
                } catch (e) {
                    reject(e);
                }
            });
        }
        resize(w, h, q,ext) {
            return new Promise((resolve, reject) => {

                this.prms.then(() => {
                    const sizes = this.calculate(w, h);
                    let url;
                    if (this.isLocal === true && this.isBig !== true && this.ext !== 'svg') {
                        this.canvas.width = sizes.w = Math.trunc(sizes.w);
                        this.canvas.height = sizes.h = Math.trunc(sizes.h);
                        const flip = this.effects.get('flip');
                        if (flip) {
                            this._setFlip(flip.v, flip.h, sizes.w, sizes.h);
                        }
                        this.ctx.drawImage(this.tmpImg, sizes.x, sizes.y, sizes.w, sizes.h, 0, 0, sizes.w, sizes.h);
                        if (q === und) {
                            q = this.q;
                        }
                        if(!ext){
                            ext=this.webp;
                        }
                        url = this.canvas.toDataURL(ext, q);
                        this.img.setAttribute('src', url);
                    } else {
                        url = this.url;
                    }
                    if (w) {
                        w = Math.trunc(w);
                        this.img.setAttribute('width', w);
                        if (this.img.style.width) {
                            this.img.style.width = w + 'px';
                        }
                    } else {
                        this.img.removeAttribute('width');
                    }
                    if (h) {
                        h = Math.trunc(h);
                        this.img.setAttribute('height', h);
                        if (this.img.style.height) {
                            this.img.style.height = h + 'px';
                        }
                    } else {
                        this.img.removeAttribute('height');
                    }
                    resolve([url, w, h, sizes.w, sizes.h]);

                })
                        .catch(reject);
            });
        }
        toBlob(w, h, q,ext) {
            return new Promise(async(resolve, reject) => {
                try {
                    await this.prms;
                    if (this.isLocal === false || this.isBig === true || this.ext === 'svg') {
                        resolve();
                        return;
                    }
                    if (!q) {
                        q = this.q;
                    }
                    this.ctx.imageSmoothingEnabled = q > .7;
                    this.ctx.imageSmoothingQuality = q > .7 ? 'high' : (q > .5 ? 'medium' : 'low');
                    await this.resize(w, h, q,ext);
                    const url = await this.save(q,ext);
                    resolve(url);
                } catch (e) {
                    reject(e);
                }
            });
        }
        flip(vertical) {
            return new Promise(async(resolve, reject) => {
                try {
                    await this.prms;
                    if (this.isLocal === false || this.isBig === true || this.ext === 'svg') {
                        resolve();
                        return;
                    }
                    const k = 'flip',
                        type = vertical ? 'v' : 'h',
                        v = this._getEffect(k) || {},
                        flipV = vertical ? 1 : false,
                        flipH = !vertical ? 1 : false,
                        w = this.w,
                        h = this.h;

                    this.canvas.width = w;
                    this.canvas.height = h;
                    this.ctx.imageSmoothingEnabled = true;
                    this.ctx.imageSmoothingQuality = 'high';
                    this._setFlip(flipV, flipH, w, h);
                    this.ctx.drawImage(this.tmpImg, 0, 0, w, h, 0, 0, w, h);
                    if (v[type] !== und) {
                        delete v[type];
                    } else {
                        v[type] = 1;
                    }
                    this._setEffect(k, v);
                    console.log(v[type]);
                    await this.save(1, this.ext);
                    resolve();
                } catch (e) {console.log(e);
                    reject(e);
                }
            });
        }
        _setFlip(flipV, flipH, w, h) {
            this.ctx.setTransform((flipH ? -1 : 1), 0, 0, (flipV ? -1 : 1), (flipH ? w : 0), (flipV ? h : 0));
        }
        filter(eff,value){
            return new Promise(async(resolve, reject) => {
                try {
                    await this.prms;
                    if (this.isLocal === false || this.isBig === true || this.ext === 'svg') {
                        resolve();
                        return;
                    }
                    const w = this.w,
                        h = this.h;
                    if(!this.filterEff){
                        this.canvas.width = w;
                        this.canvas.height = h;
                        this.filterEff = new WebGLImageFilter();
                    }
                    this.ctx.drawImage(this.tmpImg, 0, 0, w, h, 0, 0, w, h);
                    if(value>1){
                        value=parseFloat(value/100);
                    }
                    this.filterEff.addFilter(eff, value);
                    const filteredImage = this.filterEff.apply(this.canvas);
                    this.ctx.drawImage(filteredImage, 0, 0, w, h, 0, 0, w, h);
                    this.img.setAttribute('src', this.canvas.toDataURL(this.webp, this.q));
                    resolve();
                } 
                catch (e) {
                    reject(e);
                }
            });
        }
        _getEffect(k, update) {
            if (this.effects === und || update === true) {
                this.effects = this.img.getAttribute('data-tf-effects');
                this.effects = this.effects ? JSON.parse(this.effects) : {};
                this.effects = new Map(Object.entries(this.effects));
            }
            return k ? this.effects.get(k) : this.effects;
        }
        _setEffect(k, v) {
            this.effects = this._getEffect(false, true);
            if (v !== und && v !== false && v !== '' && (typeof v !== 'object' || Object.keys(v).length > 0)) {
                this.effects.set(k, v);
            } else {
                this.effects.delete(k);
            }
                if (this.effects.size > 0) {
                    this.img.setAttribute('data-tf-effects', JSON.stringify(Object.fromEntries(this.effects)));
                } else {
                    this.img.removeAttribute('data-tf-effects');
                }
        }
        updateEffects() {
            this._getEffect(false, true);
        }
        _applyEfects(url) {
            return new Promise(resolve => {
                this.effects = this._getEffect();
                resolve();
            });
        }
        save(q, ext) {
            return new Promise((resolve, reject) => {
                this.prms.then(() => {
                    if (!ext) {
                        ext = this.webp;
                    }
                    this.canvas.toBlob(blob => {
                        try {
                            const url = URL.createObjectURL(blob, ext);
                            this.img.tfOn('load', () => {
                                const oldUrl = objects.get(this.img);
                                //console.trace(oldUrl);
                                if (oldUrl) {
                                    URL.revokeObjectURL(oldUrl);
                                }
                                objects.set(this.img, url);
                                resolve(url);
                            }, {passive: true, once: true});
                            this.img.src = url;
                        } catch (e) {
                            reject(e);
                        }
                    }, ext, q);
                })
                        .catch(reject);
            });
        }
        calculate(w, h) {
            if (w) {
                w = Math.trunc(w);
            }
            if (h) {
                h = Math.trunc(h);
            }
            let dest_w = w,
                    dest_h = h,
                    crop_w = w,
                    crop_h = h;

            if (!dest_w) {
                dest_w = w = Math.trunc(dest_h * this.aspectRatio);
            }

            if (!dest_h) {
                dest_h = h = Math.trunc(dest_w / this.aspectRatio);
            }
            const v1 = dest_w / this.w,
                    v2 = dest_h / this.h,
                    size_ratio = v1 > v2 ? v1 : v2;

            crop_w = ((dest_w / size_ratio) + .5) >> 0; //round
            crop_h = ((dest_h / size_ratio) + .5) >> 0; //round

            const x = ((this.w - crop_w) / 2) >> 0, //floor
                    y = ((this.h - crop_h) / 2) >> 0; //floor
            return {x: x, y: y, w: crop_w, h: crop_h};
        }
    }
    const resize = (img, w, h, isBlob) => {
        if (!w && !h) {
            return Promise.resolve();
        }
        let obj = new ThemifyImageResize(img, 1),
                prms,
                loader;
        if (obj.isLocal === true && obj.isBig !== true && img.isConnected) {
            loader = doc.createElement('span');
            loader.className = 'tf_loader tf_abs_c';
            img.after(loader);
        }
        prms = isBlob ? obj.toBlob(w, h, 1) : obj.resize(w, h, 1);
        return prms.finally(() => {
            if (loader) {
                loader.remove();
                loader = null;
            }
            if (obj.canvas) {
                obj.ctx.clearRect(0, 0, obj.canvas.width, obj.canvas.height);
                obj.canvas = obj.tmpImg = obj.origImg = obj.ctx = null;
            }
            obj = null;
        });
    };
    ThemifyImageResize.toBlob = (img, w, h) => {
        return resize(img, w, h, true);
    };
    ThemifyImageResize.resize = resize;
    ThemifyImageResize.clear = clearAll => {
        for (let [el, url] of objects) {
            if (clearAll === true || !el.isConnected) {
                URL.revokeObjectURL(url);
                objects.delete(el);
            }
        }
    };
    setInterval(ThemifyImageResize.clear, 180000);//clear memory

})(Themify, document, undefined);
