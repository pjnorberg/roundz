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
            playoffSize: 0,
            tournamentMatchCount: 0
        },
        methods: {
            resetApp: function resetApp() {
                this.qualifyingMatches = [];
                this.playoffMatches = [];
                this.tournamentSize = 0;
                this.playoffSize = 0;
                this.tournamentMatchCount = 0;
            },
            addParticipant: function addParticipant(ev) {
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
            deleteParticipant: function deleteParticipant(index) {
                var participantId = this.tournament.participants[index].id;
                this.$resource('/participants/' + participantId).delete({ _token: this.tournament.token }).then(function (response) {
                    if (response.data.response == 'success') {
                        this.tournament.participants.splice(index, 1);
                        this.resetApp();
                    }
                });
            },
            generateMatches: function generateMatches() {
                // Get everyone in this tournament and shuffle this list randomly:
                var totalPlayerCount = this.tournament.participants.length;
                this.shuffleParticipants();

                if (this.tournamentSizeOK(totalPlayerCount)) {
                    // If tournament size is OK, go ahead and make a playoff:
                    this.playoffSize = totalPlayerCount;
                    this.tournamentSize = totalPlayerCount;
                    this.calculateGames(0);
                    this.makePlayoff(this.playoffSize, this.tournamentMatchCount);
                    this.addParticipantsToPlayoff();
                } else {
                    // Otherwise we need qualifying rounds:
                    this.tournamentSize = this.findTournamentSize(totalPlayerCount);
                    this.generateQualifyingRound();

                    // And then we can make a playoff as well.
                }
            },
            generateQualifyingRound: function generateQualifyingRound() {
                // The idea is that we have a tournament size (lets say 8), which is the closest value to the
                // total number of players available (lets say 12). Then our qualifying round will just be a
                // matter of having everyone play one game and the top 8 will be transferred to the playoff.
                // (If uneven number of total players, one random player will be automatically qualified and
                // and then the top 7 will enter the playoff.
                var qualifiedPositions = this.tournamentSize;
                console.log(qualifiedPositions);

                // If this is an uneven count, subtract one and have a random player be directly qualified:
                if (this.tournamentSize % 2 == 1) {
                    this.directlyQualifyPlayer();
                }
            },
            directlyQualifyPlayer: function directlyQualifyPlayer() {
                var id = arguments.length <= 0 || arguments[0] === undefined ? null : arguments[0];
            },
            addParticipantsToPlayoff: function addParticipantsToPlayoff() {
                // We have lets say 15 games, for 16 players. That means 8 games, or 1 per 2 players first round!
                var firstRound = this.playoffSize / 2;

                // Since we have shuffled our player list, connecting them to a game should be easy enough.
                for (var i = 0; i < firstRound; i++) {
                    this.playoffMatches[i].home_participant_id = this.tournament.participants[i + i].id;
                    this.playoffMatches[i]._home_name = this.tournament.participants[i + i].name;
                    this.playoffMatches[i].away_participant_id = this.tournament.participants[i + i + 1].id;
                    this.playoffMatches[i]._away_name = this.tournament.participants[i + i + 1].name;
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
            makePlayoff: function makePlayoff(participantsCount, maxGames) {
                var matchId = arguments.length <= 2 || arguments[2] === undefined ? 0 : arguments[2];
                var round = arguments.length <= 3 || arguments[3] === undefined ? 1 : arguments[3];
                var playedMatchIds = arguments.length <= 4 || arguments[4] === undefined ? [] : arguments[4];


                if (!matchId) {
                    this.playoffMatches = [];
                }

                // Take the list of players and divide by 2 to get no. of matches needed for round 1.
                // Then recursively divide by 2 until 1 (the final).

                // Reset count of previous match IDs to collect only this round later:
                var previousRoundMatchIds = playedMatchIds.length > 0 ? playedMatchIds : [];
                playedMatchIds = [];

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

                return true;
            },
            /**
             * Create matches (without teams assigned) but linked to previous matches (if
             * @param id
             * @param round
             * @param home
             * @param away
             * @param playoff
             */
            createMatch: function createMatch(id, round, home, away) {
                var playoff = arguments.length <= 4 || arguments[4] === undefined ? true : arguments[4];

                // 'id', 'home_team_from' and 'away_team_from' are all relative IDs generated by this application.
                // Underscored versions are the database-persisted versions.
                var match = {
                    _id: null,
                    _home_team_from: null,
                    _away_team_from: null,
                    id: id,
                    round: 0,
                    home_team_from: null,
                    away_team_from: null,
                    home_participant_id: null,
                    away_participant_id: null,
                    playoff: playoff,
                    home_score: 0,
                    away_score: 0,
                    finished: 0
                };

                if (playoff) {
                    match.home_team_from = home;
                    match.away_team_from = away;
                    match.round = round;
                }

                this.playoffMatches.push(match);
                console.log('match ' + id + ', round ' + round + ', winners from matches ' + home + ' vs ' + away);
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
