(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

/**
 * Round Z app
 */

$(function () {
    new Vue({
        el: '#app',
        data: {
            actionStatus: '',
            tournament: tournament,
            qualifyingMatches: [],
            playoffMatches: [],
            tournamentSize: 0,
            tournamentMatchCount: 0
        },
        methods: {
            addParticipant: function addParticipant(ev) {
                var newParticipant = ev.target.value.trim();
                if (newParticipant) {
                    this.$resource('/api/').save({
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
            deleteParticipant: function deleteParticipant(index) {
                this.setStatus('Deleting participant');
                var participantId = this.tournament.participants[index].id;
                this.$resource('/api/' + participantId).delete({ _token: this.tournament.token }).then(function (response) {
                    if (response.data.response == 'success') {
                        this.tournament.participants.splice(index, 1);
                    }
                });
            },
            generateMatches: function generateMatches() {
                var totalPlayerCount = this.tournament.participants.length;
                this.shuffleParticipants();

                if (this.tournamentSizeOK(totalPlayerCount)) {
                    // If tournament size is OK, go ahead and make a playoff:
                    this.setStatus('Making games for playoff...');
                    this.tournamentSize = totalPlayerCount;
                    this.calculateGames(0);
                    this.makePlayoff(this.tournament.participants.length, this.tournamentMatchCount);
                } else {
                    // Otherwise we need qualifying rounds:
                    this.tournamentSize = this.findTournamentSize(totalPlayerCount);

                    // The idea is that we have a tournament size (lets say 8), which is the closest value to the
                    // total number of players available (lets say 12). Then our qualifying round will just be a
                    // matter of having everyone play one game and the top 8 will be transfered to the playoff.
                    // (If uneven number of total players, one random player will be automatically qualified and
                    // and then the top 7 will enter the playoff.
                }
            },
            makePlayoff: function makePlayoff(participantsCount, maxGames) {
                var matchId = arguments.length <= 2 || arguments[2] === undefined ? 100 : arguments[2];
                var round = arguments.length <= 3 || arguments[3] === undefined ? 1 : arguments[3];
                var playedMatchIds = arguments.length <= 4 || arguments[4] === undefined ? [] : arguments[4];

                // Take the list of players and divide by 2 to get no. of matches needed for round 1.
                // Then recursively divide by 2 until 1 (the final).

                var previousRoundMatchIds = playedMatchIds.length > 0 ? playedMatchIds : [];
                playedMatchIds = [];

                console.log(previousRoundMatchIds);

                // Make this round of games:
                participantsCount = participantsCount / 2;
                for (var i = 0; i < participantsCount; i++) {
                    var home = null,
                        away = null;

                    if (round > 1 && previousRoundMatchIds.length > 0) {
                        home = previousRoundMatchIds[i + i];
                        away = previousRoundMatchIds[i + i + 1];
                    }

                    this.createMatch(matchId, round, home, away);
                    playedMatchIds.push(matchId);
                    matchId++;
                }

                // Count down to zero:
                maxGames -= participantsCount;

                // Keep going if we still have games to make:
                if (maxGames) {
                    return this.makePlayoff(participantsCount, maxGames, matchId, ++round, playedMatchIds);
                }

                console.log(this.playoffMatches);
                return true;
            },
            createMatch: function createMatch(id, round, home, away) {
                var playoff = arguments.length <= 4 || arguments[4] === undefined ? true : arguments[4];

                this.playoffMatches.push({
                    id: id,
                    round: round,
                    home_match_winner_id: home,
                    away_match_winner_id: away,
                    playoff: playoff
                });
                //console.log('match ' + id + ', round ' + round + ', winners from matches ' + home + ' vs ' + away);
            },
            /**
             * Check if number is a workable size for a tournament.
             * @param number
             * @returns {*}
             */
            tournamentSizeOK: function tournamentSizeOK(number) {
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
            findTournamentSize: function findTournamentSize(participants) {
                if (!this.tournamentSizeOK(participants)) {
                    participants = participants - 1;
                    return this.findTournamentSize(participants);
                }
                return participants;
            },
            /**
             * Randomize the list of participants.
             */
            shuffleParticipants: function shuffleParticipants() {
                var array = this.tournament.participants;
                var currentIndex = array.length,
                    temporaryValue,
                    randomIndex;
                while (0 !== currentIndex) {
                    randomIndex = Math.floor(Math.random() * currentIndex);
                    currentIndex -= 1;
                    temporaryValue = array[currentIndex];
                    array[currentIndex] = array[randomIndex];
                    array[randomIndex] = temporaryValue;
                }
                this.tournament.participants = array;
            },
            setStatus: function setStatus() {
                var text = arguments.length <= 0 || arguments[0] === undefined ? false : arguments[0];
                var maxDuration = arguments.length <= 1 || arguments[1] === undefined ? 5000 : arguments[1];

                this.actionStatus = text ? '<i class="fa fa-cog fa-spin"></i> &nbsp; ' + text : '';
            },
            /**
             * Calculate number of matches in this tournament (based on tournamentSize).
             * @param matches
             * @returns {*}
             */
            calculateGames: function calculateGames() {
                var matches = arguments.length <= 0 || arguments[0] === undefined ? 0 : arguments[0];

                if (!matches) {
                    matches = this.tournamentSize;
                    this.tournamentMatchCount = 0;
                }
                if (matches > 1) {
                    matches = matches / 2;
                    this.tournamentMatchCount += matches;
                    return this.calculateGames(matches);
                }
                return true;
            }
        }
    });
});

},{}]},{},[1]);

//# sourceMappingURL=app.js.map
