/**
 * Round Z app
 */

$(function() {
    new Vue({
        el: '#app',
        data: {
            tournamentId: null,
            participants: {},
            participantCount: 0
        },
        created: function () {
            // Load participants:
            $.getJSON('/api/?tournament_id=' + this.tournamentId, null, function (participants) {
                this.participants = participants;
            });
        },
        methods: {
        }
    });
});