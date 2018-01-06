<?php $view->script('settings', 'spqr/wordpressimport:app/bundle/settings.js',
    ['vue', 'uikit-upload']); ?>

<div id="settings" class="uk-form uk-form-horizontal" v-cloak>
	<div class="uk-grid pk-grid-large" data-uk-grid-margin>
		<div class="pk-width-sidebar">
			<div class="uk-panel">
				<ul class="uk-nav uk-nav-side pk-nav-large"
				    data-uk-tab="{ connect: '#tab-content' }">
					<li>
						<a><i class="pk-icon-large-settings
                        uk-margin-right"></i> {{ 'XML Upload' | trans }}</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="pk-width-content">
			<ul id="tab-content" class="uk-switcher uk-margin">
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap"
					     data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'XML Upload' |
								trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary"
							        @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<div class="uk-form-row">
						<label class="uk-form-label">{{ 'File' | trans }}</label>
						<div data-uk-margin>
							<file-upload type="xml"></file-upload>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>