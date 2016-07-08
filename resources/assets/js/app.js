/**
 * Round Z app
 */

$(function() {
    new Vue({
        el: '#app',
        data: {
            actionStatus: '',
            tournament: tournament,
            qualifyingMatches: [],
            playoffMatches: [],
            tournamentSize: 0,
            playoffSize: 0,
            tournamentMatchCount: 0
        },
        methods: {
            resetApp: function () {
                this.qualifyingMatches = [];
                this.playoffMatches = [];
                this.tournamentSize = 0;
                this.playoffSize = 0;
                this.tournamentMatchCount = 0;
            },
            addParticipant: function (ev) {
                var newParticipant = ev.target.value.trim();
                if (newParticipant) {
                    this.$resource('/participants/').save({
                        name: newParticipant,
                        tournament_id: this.tournament.id,
                        _token: this.tournament.token
                    }).then(function (response) {
                        if (response.data.response == 'success') {
                            this.tournament.participants.push({
                                id: response.data.id,
                                name: newParticipant,
                                points: 0
                            });
                        }
                    });
                }
                ev.target.value = '';
            },
            deleteParticipant: function (index) {
                var participantId = this.tournament.participants[index].id;
                this.$resource('/participants/' + participantId).delete({ _token: this.tournament.token }).then(function (response) {
                    if (response.data.response == 'success') {
                        this.tournament.participants.splice(index, 1);
                        this.resetApp();
                    }
                });
            },
            persistMatches: function () {
                var matches = {
                    tournamentId: this.tournament.id,
                    playoffMatches: this.playoffMatches,
                    qualifyingMatches: this.qualifyingMatches,
                    playoffSize: this.tournamentSize,
                    _token: this.tournament.token
                };

                this.$resource('/matches/').save(matches).then(function (response) {
                    if (response.data.response == 'success') {
                        this.setFinished('Matches successfully created! Refresh page to view matches.');
                        this.resetApp();
                    }
                });
            },
            generateMatches: function () {
                // Get everyone in this tournament and shuffle this list randomly:
                this.resetApp();
                var totalPlayerCount = this.tournament.participants.length;
                this.shuffleParticipants();

                if (this.tournamentSizeOK(totalPlayerCount)) {
                    // If tournament size is OK, go ahead and make a playoff:
                    this.playoffSize = totalPlayerCount;
                    this.tournamentSize = totalPlayerCount;
                    this.calculateGames(0);
                    this.makePlayoff(
                        this.playoffSize,
                        this.tournamentMatchCount
                    );
                    this.addParticipantsToPlayoff();
                }
                else {
                    // Otherwise we need qualifying rounds:
                    this.tournamentSize = this.findTournamentSize(totalPlayerCount);
                    this.generateQualifyingRound();

                    // And then we can make a playoff as well:
                    this.calculateGames(0);
                    this.makePlayoff(
                        this.tournamentSize,
                        this.tournamentMatchCount
                    );
                }
            },
            /**
             * Make a qualifiying round of matches.
             * @param matchId
             */
            generateQualifyingRound: function (matchId = 0) {
                this.qualifyingMatches = [];

                // The idea is that we have a tournament size (lets say 8), which is the closest value to the
                // total number of players available (lets say 12). Then our qualifying round will just be a
                // matter of having everyone face every one else and the top 8 will be transferred to the playoff.
                var gameCounter = 0;

                for (var i = 0; i < this.tournament.participants.length; i++) {
                    // Last round of games!
                    if (i+1 < this.tournament.participants.length) {
                        var currentIndex = i;
                        var nextIndex = i+1;
                    }
                    else {
                        var currentIndex = i;
                        var nextIndex = 0;
                    }
                    this.createQualifyingMatch(
                        matchId,
                        this.tournament.participants[currentIndex].id,
                        this.tournament.participants[currentIndex].name,
                        this.tournament.participants[nextIndex].id,
                        this.tournament.participants[nextIndex].name
                    );
                }
            },
            /**
             * Add participants to all playoff places (round = 1).
             */
            addParticipantsToPlayoff: function () {
                // We have lets say 15 games, for 16 players. That means 8 games, or 1 per 2 players first round!
                var firstRound = (this.playoffSize / 2);

                // Since we have shuffled our player list, connecting them to a game should be easy enough.
                for (var i = 0; i < firstRound; i++) {
                    this.playoffMatches[i].home_participant_id = this.tournament.participants[i+i].id;
                    this.playoffMatches[i]._home_name = this.tournament.participants[i+i].name;
                    this.playoffMatches[i].away_participant_id = this.tournament.participants[i+i+1].id;
                    this.playoffMatches[i]._away_name = this.tournament.participants[i+i+1].name;
                }
            },
            /**
             * Create a playoff, by recursively adding matches per round.
             * @param participantsCount
             * @param maxGames
             * @param matchId
             * @param round
             * @param playedMatchIds
             * @returns {*}
             */
            makePlayoff: function (participantsCount, maxGames, matchId = 0, round = 1, playedMatchIds = []) {

                if ( ! matchId) {
                    this.playoffMatches = [];
                }

                // Take the list of players and divide by 2 to get no. of matches needed for round 1.
                // Then recursively divide by 2 until 1 (the final).

                // Reset count of previous match IDs to collect only this round later:
                var previousRoundMatchIds = (playedMatchIds.length > 0 ? playedMatchIds : []);
                playedMatchIds = [];

                // Make this round of games:
                participantsCount = (participantsCount / 2);
                for (var i = 0; i < participantsCount; i++) {
                    var home = null,
                        away = null;

                    if (round > 1 && previousRoundMatchIds.length > 0) {
                        home = previousRoundMatchIds[i+i];
                        away = previousRoundMatchIds[i+i+1];
                    }

                    this.createPlayoffMatch(matchId, round, home, away);
                    playedMatchIds.push(matchId);
                    matchId++;
                }

                // Count down to zero:
                maxGames -= participantsCount;

                // Keep going if we still have games to make:
                if (maxGames) {
                    return this.makePlayoff(participantsCount, maxGames, matchId, ++round, playedMatchIds);
                }

                return true;
            },
            createPlayoffMatch: function (id, round, homeFrom, awayFrom) {
                // 'id', 'home_team_from' and 'away_team_from' are all relative IDs generated by this application.
                // Underscored versions are the database-persisted versions.
                var match = {
                    _id: null,
                    _home_team_from: null,
                    _away_team_from: null,
                    id: id,
                    round: round,
                    home_team_from: homeFrom,
                    away_team_from: awayFrom,
                    home_participant_id: null,
                    away_participant_id: null,
                    playoff: 1,
                    home_score: 0,
                    away_score: 0,
                    finished: 0
                };

                this.playoffMatches.push(match);
            },
            createQualifyingMatch: function (id, homeId, homeName, awayId, awayName) {
                // 'id', 'home_team_from' and 'away_team_from' are all relative IDs generated by this application.
                // Underscored versions are the database-persisted versions.
                var match = {
                    _id: null,
                    _home_team_from: null,
                    _away_team_from: null,
                    _home_name: homeName,
                    _away_name: awayName,
                    id: id,
                    round: 0,
                    home_team_from: null,
                    away_team_from: null,
                    home_participant_id: homeId,
                    away_participant_id: awayId,
                    playoff: 0,
                    home_score: 0,
                    away_score: 0,
                    finished: 0
                };

                this.qualifyingMatches.push(match);
            },
            /**
             * Check if number is a workable size for a tournament.
             * @param number
             * @returns {*}
             */
            tournamentSizeOK: function (number) {
                if (number > 1) {
                    number = number / 2;
                    return this.tournamentSizeOK(number);
                }
                return number < 1 ? false : true;
            },
            /**
             * Get the workable size of a tournament, i.e. 2/4/8/16 players for total no of participants.
             * @param participants
             * @returns {*}
             */
            findTournamentSize: function (participants) {
                if ( ! this.tournamentSizeOK(participants)) {
                    participants = participants - 1;
                    return this.findTournamentSize(participants);
                }
                return participants;
            },
            /**
             * Randomize the list of participants.
             */
            shuffleParticipants: function () {
                var array = this.tournament.participants;
                var currentIndex = array.length, temporaryValue, randomIndex;
                while (0 !== currentIndex) {
                    randomIndex = Math.floor(Math.random() * currentIndex);
                    currentIndex -= 1;
                    temporaryValue = array[currentIndex];
                    array[currentIndex] = array[randomIndex];
                    array[randomIndex] = temporaryValue;
                }
                this.tournament.participants = array;
            },
            setWorking: function (text = false) {
                this.actionStatus = (text ? '<div class="alert"><i class="fa fa-cog fa-spin"></i> &nbsp; ' + text + '</div>' : '')
            },
            setFinished: function (text = false) {
                this.actionStatus = (text ? '<div class="alert"><i class="fa fa-check-circle"></i> &nbsp; ' + text + '</div>' : '')
            },
            /**
             * Calculate number of matches in this tournament (based on tournamentSize).
             * @param matches
             * @returns {*}
             */
            calculateGames: function (matches = 0) {
                if ( ! matches) {
                    matches = this.tournamentSize;
                    this.tournamentMatchCount = 0;
                }
                if (matches > 1) {
                    matches = (matches / 2);
                    this.tournamentMatchCount += matches;
                    return this.calculateGames(matches);
                }
                return true;
            },
            updateScore: function (event, matchId, whoScored) {
                data = {
                    _token: this.tournament.token
                };

                if (whoScored == 'home') {
                    data.home_score = event.target.value;
                }
                else if (whoScored == 'away') {
                    data.away_score = event.target.value;
                }

                this.$resource('/matches/' + matchId).update(data).then(function (response) {
                    if (response.data.success) {
                    }
                    else {
                    }
                });
            },
            finishGame: function (matchId) {
                data = {
                    finished: true,
                    _token: this.tournament.token
                };

                this.$resource('/matches/' + matchId).update(data).then(function (response) {
                    if (response.data.success) {
                        // Remove button:
                        var elem = document.getElementById('endGame-'+matchId);
                        elem.parentNode.removeChild(elem);
                        // Change status:
                        document.getElementById('gameStatus-'+matchId).innerHTML = 'Finished';
                    }
                });
            }
        }
    });
});