<template>
    <div class="uk-modal-header uk-flex uk-flex-middle">
        <div class="uk-flex-item-1">
            <h2 class="uk-margin-small-bottom">{{ 'WordPress XML Import' | trans }}</h2>
            <ul class="uk-subnav uk-subnav-line uk-margin-bottom-remove">
                <li class="uk-text-muted">{{ file.path }}</li>
                <li class="uk-text-muted">{{ file.filename }}</li>
            </ul>
        </div>
    </div>
    <p>{{ 'This tool tries to import all your blog entries of your old WordPress installation. Please do not close this window, until the success-message appears.' | trans }}</p>
</template>

<script>


    module.exports = {

        props: {
            file: {
                type: Object,
                default: function () {
                    return {};
                }
            }
        },

        data: function () {
            return {
                messages: {}
            };
        },

        filters: {
        },

        watch: {
        },

        methods: {
        }

    }

</script>