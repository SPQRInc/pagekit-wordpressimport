window.settings = {

	el: '#settings',

	data: {
		config: $data.config,
		file: {},
	},

	methods: {
		save: function () {
			this.$http.post('admin/wordpressimport/save', {config: this.config}, function () {
				this.$notify('Settings saved.');
			}).error(function (data) {
				this.$notify(data, 'danger');
			});
		}
	},
	components: {
		'file-upload': require('../../components/file-upload.vue')
	}
};

Vue.ready(window.settings);
