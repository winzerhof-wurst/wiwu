/**
 * Winzerhof Wurst – www.winzerhof-wurst.at
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Christoph Wurst 2017
 */

define(function(require) {
	'use strict';

	var $ = require('jquery');
	var _ = require('underscore');

	var TidbitCollection = require('../collections/tidbitcollection');

	/**
	 * @class TidbitService
	 */
	var TidbitService = function(options) {
		this.initialize(options);
	};

	TidbitService.prototype = {
		/**
		 * @type {TidbitCollection}
		 */
		_collection: undefined,

		/**
		 * @returns {undefined}
		 */
		initialize: function() {
			this._collection = new TidbitCollection();

			_.bindAll(this, 'getAll');
		},

		/**
		 * @returns {unresolved}
		 */
		getAll: function() {
			var defer = $.Deferred();

			this._collection.fetch({
				success: function(data) {
					defer.resolve(data);
				},
				error: function() {
					defer.reject();
				}
			});

			return defer.promise();
		}
	};

	return TidbitService;
});
