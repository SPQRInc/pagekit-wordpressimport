<template>
    <a class="uk-button uk-button-primary uk-form-file">
        <span v-show="!progress">{{ 'Upload' | trans }}</span>
        <span v-else><i class="uk-icon-spinner uk-icon-spin"></i> {{ progress }}</span>
        <input type="file" name="file" v-el:input>
    </a>
    <div class="uk-modal" v-el:modal>
        <div class="uk-modal-dialog">
            <file-details :file="file"></file-details>
            <div class="uk-modal-footer uk-text-right">
                <button class="uk-button uk-button-link uk-modal-close" type="button">{{ 'Cancel' | trans }}</button>
                <button class="uk-button uk-button-link"
                @click.prevent="doImport">{{ 'Import' | trans }}
                </button>
            </div>

        </div>
    </div>
</template>

<script>

let Importer = Vue.extend (require ('../lib/importer.vue'));

module.exports = {


	props: {
		type: String
	},

	data: function () {
		return {
			file: {},
			upload: null,
			progress: ''
		};
	},

	ready: function () {

		let type = this.type,
			settings = {
				action: this.$url.route ('admin/wordpressimport/upload'),
				type: 'json',
				param: 'file',
				before: function (options) {
					_.merge (options.params, {_csrf: $pagekit.csrf, type: type});
				},
				loadstart: this.onStart,
				progress: this.onProgress,
				allcomplete: this.onComplete
			};

		UIkit.uploadSelect (this.$els.input, settings);

		this.modal = UIkit.modal (this.$els.modal);
	},

	methods: {

		onStart: function () {
			this.progress = '1%';
		},

		onProgress: function (percent) {
			this.progress = Math.ceil (percent) + '%';
		},

		onComplete: function (data) {

			let vm = this;
			this.progress = '100%';

			setTimeout (function () {
				vm.progress = '';
			}, 250);

			if (!data.file) {
				this.$notify (data, 'danger');
				return;
			}

			this.$set ('upload', data);
			this.$set ('file', data.file);
			this.modal.show ();
		},

		doImport: function () {

			this.modal.hide ();

			this.import (this.upload.file,
				function (output) {
					if (output.status === 'success') {
						setTimeout (function () {
							location.reload ();
						}, 300);
					}
				}, true);
		},

		import: function (file, onClose) {
			let importer = new Importer({parent: this});

			return importer.import(file, onClose);
		},
	},

    components: {
		'file-details': require('./file-details.vue')
	}
};

</script>