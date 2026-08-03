/* Zorgkosten Calculator – frontend stepper
 * Renders the cost calculator from the JSON config produced by the Elementor
 * widget. No dependencies.
 *
 * The flow has 9 steps, but the authorization ("machtiging") step is only
 * part of it when the chosen insurer needs one — for every other insurer the
 * flow is 8 steps and the counter adjusts accordingly.
 */
(function () {
	'use strict';

	var BASE_STEPS = [
		'insurer',
		'policy',
		'deductible',
		'used',
		'info',
		'reimbursement',
		'invoices',
		'machtiging',
		'coulance'
	];

	function fmt(amount) {
		try {
			return '€ ' + new Intl.NumberFormat('nl-NL', { maximumFractionDigits: 0 }).format(Math.round(amount));
		} catch (e) {
			return '€ ' + Math.round(amount);
		}
	}

	function el(tag, cls, html) {
		var node = document.createElement(tag);
		if (cls) node.className = cls;
		if (html !== undefined && html !== null) node.innerHTML = html;
		return node;
	}

	function esc(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function tpl(str, map) {
		return String(str == null ? '' : str).replace(/\{(\w+)\}/g, function (m, key) {
			return Object.prototype.hasOwnProperty.call(map, key) ? map[key] : m;
		});
	}

	function Calculator(root) {
		var raw = root.getAttribute('data-zkc-config');
		if (!raw) return;
		try {
			this.cfg = JSON.parse(raw);
		} catch (e) {
			return;
		}
		this.root = root;
		this.inner = root.querySelector('.zkc-inner') || root;
		this.state = this.blankState();
		this.render();
	}

	Calculator.prototype.blankState = function () {
		return {
			screen: 'intro',      // 'intro' | a step key | 'result'
			insurer: null,
			policy: null,         // policy object, 'unknown', or null
			deductible: null,     // number, 'unknown', or null
			usedDeductible: 0,
			usedUnknown: false
		};
	};

	/* ------------------------------------------------------------------ flow */

	/* The machtiging step only exists for insurers that may need one. */
	Calculator.prototype.steps = function () {
		var needsMachtiging = !!(this.state.insurer && this.state.insurer.machtiging);
		return BASE_STEPS.filter(function (key) {
			return key !== 'machtiging' || needsMachtiging;
		});
	};

	Calculator.prototype.stepIndex = function () {
		return this.steps().indexOf(this.state.screen);
	};

	Calculator.prototype.goTo = function (screen) {
		this.state.screen = screen;
		this.render();
		var rect = this.root.getBoundingClientRect();
		if (rect.top < 0) {
			this.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	};

	Calculator.prototype.goNext = function () {
		var steps = this.steps();
		var i = steps.indexOf(this.state.screen);
		this.goTo(i === -1 || i >= steps.length - 1 ? 'result' : steps[i + 1]);
	};

	Calculator.prototype.goBack = function () {
		var steps = this.steps();
		if (this.state.screen === 'result') {
			return this.goTo(steps[steps.length - 1]);
		}
		var i = steps.indexOf(this.state.screen);
		this.goTo(i <= 0 ? 'intro' : steps[i - 1]);
	};

	Calculator.prototype.restart = function () {
		this.state = this.blankState();
		this.render();
	};

	/* ------------------------------------------------------------------ data */

	Calculator.prototype.basisInfo = function (key) {
		var bases = this.cfg.bases || [];
		for (var i = 0; i < bases.length; i++) {
			if (bases[i].key === key) return bases[i];
		}
		return { key: key, label: key, title: key, text: '' };
	};

	Calculator.prototype.policiesFor = function (insurerName) {
		return (this.cfg.policies || []).filter(function (p) {
			return p.insurer === insurerName;
		});
	};

	/* Resolves the chosen policy into a percentage label ("70" or "60–100"),
	 * the fraction used for the money (the middle of a range) and the basis. */
	Calculator.prototype.reimbursement = function () {
		var c = this.cfg.calc;
		var pol = this.state.policy;
		var unknown = !pol || pol === 'unknown';
		var min, max, fraction, label, basis, note;

		if (unknown) {
			min = c.defaultPercentage;
			fraction = min / 100;
			label = String(Math.round(min));
			basis = c.defaultBasis;
			note = '';
		} else {
			min = pol.percentage;
			max = (pol.percentageMax === null || pol.percentageMax === undefined) ? null : pol.percentageMax;
			if (max !== null) {
				fraction = (min + max) / 200;
				label = Math.round(min) + '–' + Math.round(max);
			} else {
				fraction = min / 100;
				label = String(Math.round(min));
			}
			basis = pol.basis;
			note = pol.note || '';
		}

		return {
			unknown: unknown,
			label: label,
			fraction: fraction,
			basis: basis,
			basisLabel: this.basisInfo(basis).label,
			note: note,
			expected: Math.round(c.avgInvoice * fraction)
		};
	};

	/* The own-costs figures. When either the total deductible or the amount
	 * already used is unknown we can only show a range. */
	Calculator.prototype.ownCosts = function () {
		var c = this.cfg.calc;
		var totalKnown = this.state.deductible !== null && this.state.deductible !== 'unknown';
		var known = totalKnown && !this.state.usedUnknown;

		if (known) {
			var remaining = Math.max(0, this.state.deductible - (this.state.usedDeductible || 0));
			return {
				known: true,
				deductible: remaining,
				total: c.contribution + remaining
			};
		}

		var ceiling = totalKnown ? this.state.deductible : c.maxDeductible;
		return {
			known: false,
			deductibleMin: 0,
			deductibleMax: ceiling,
			totalMin: c.contribution,
			totalMax: c.contribution + ceiling
		};
	};

	/* ---------------------------------------------------------------- render */

	Calculator.prototype.render = function () {
		var self = this;
		var g = this.cfg.general;
		this.inner.innerHTML = '';

		if (g.showHeader) {
			var header = el('div', 'zkc-header');
			header.appendChild(el('div', 'zkc-brand', esc(g.brand)));
			if (this.state.screen !== 'intro') {
				var restart = el('button', 'zkc-restart', esc(g.restart));
				restart.type = 'button';
				restart.addEventListener('click', function () { self.restart(); });
				header.appendChild(restart);
			}
			this.inner.appendChild(header);
		}

		var steps = this.steps();
		var index = this.stepIndex();
		if (index !== -1) {
			var pct = steps.length > 1 ? Math.round((index / (steps.length - 1)) * 100) : 0;
			var prog = el('div', 'zkc-progress');
			var meta = el('div', 'zkc-progress-meta');
			meta.appendChild(el('span', '', esc(tpl(g.stepCounter, {
				current: index + 1,
				total: steps.length
			}))));
			meta.appendChild(el('span', '', pct + '%'));
			prog.appendChild(meta);
			var bar = el('div', 'zkc-progress-bar');
			var fill = el('div', 'zkc-progress-fill');
			fill.style.width = pct + '%';
			bar.appendChild(fill);
			prog.appendChild(bar);
			this.inner.appendChild(prog);
		}

		var card = el('div', 'zkc-card');
		this.inner.appendChild(card);

		switch (this.state.screen) {
			case 'intro':         this.renderIntro(card); break;
			case 'insurer':       this.renderInsurers(card); break;
			case 'policy':        this.renderPolicies(card); break;
			case 'deductible':    this.renderDeductible(card); break;
			case 'used':          this.renderUsedDeductible(card); break;
			case 'info':          this.renderInfo(card); break;
			case 'reimbursement': this.renderReimbursement(card); break;
			case 'invoices':      this.renderInvoices(card); break;
			case 'machtiging':    this.renderMachtiging(card); break;
			case 'coulance':      this.renderCoulance(card); break;
			case 'result':        this.renderResult(card); break;
		}

		if (g.showFooter) {
			this.inner.appendChild(el('div', 'zkc-footer', esc(g.footer)));
		}
	};

	Calculator.prototype.helpBox = function (text) {
		if (!text) return null;
		return el('div', 'zkc-help', '<strong>' + esc(this.cfg.general.helpPrefix) + '</strong> ' + esc(text));
	};

	Calculator.prototype.navRow = function (card, opts) {
		var self = this;
		opts = opts || {};
		var row = el('div', 'zkc-nav');
		var back = el('button', 'zkc-btn zkc-btn-ghost', esc(this.cfg.general.back));
		back.type = 'button';
		back.addEventListener('click', function () { self.goBack(); });
		row.appendChild(back);
		if (opts.next) {
			var next = el('button', 'zkc-btn zkc-btn-primary', esc(opts.nextLabel || this.cfg.general.next));
			next.type = 'button';
			next.addEventListener('click', opts.next);
			row.appendChild(next);
		}
		card.appendChild(row);
	};

	/* Intro */
	Calculator.prototype.renderIntro = function (card) {
		var self = this;
		var i = this.cfg.intro;
		var wrap = el('div', 'zkc-intro');
		wrap.appendChild(el('div', 'zkc-kicker', esc(i.kicker)));
		wrap.appendChild(el('h2', 'zkc-title zkc-intro-title', esc(i.title)));
		wrap.appendChild(el('p', 'zkc-intro-text', esc(i.text)));
		var btn = el('button', 'zkc-btn zkc-btn-primary', esc(i.button));
		btn.type = 'button';
		btn.addEventListener('click', function () { self.goTo('insurer'); });
		wrap.appendChild(btn);
		card.appendChild(wrap);
	};

	/* Step 1 — insurer */
	Calculator.prototype.renderInsurers = function (card) {
		var self = this;
		var s = this.cfg.steps.s1;
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));
		var help = this.helpBox(s.help);
		if (help) card.appendChild(help);

		// Group insurers preserving order.
		var groups = [];
		var byName = {};
		(this.cfg.insurers || []).forEach(function (ins) {
			var key = ins.group || '';
			if (!byName[key]) {
				byName[key] = { name: key, insurers: [] };
				groups.push(byName[key]);
			}
			byName[key].insurers.push(ins);
		});

		var colCount = this.columnCount();
		this._lastColCount = colCount;

		// Distribute groups over columns (in order, balanced by estimated height).
		var weights = groups.map(function (g) {
			return 1.4 + Math.ceil(g.insurers.length / 3) * 1.9;
		});
		var total = 0;
		weights.forEach(function (w) { total += w; });
		var target = total / colCount;

		var wrap = el('div', 'zkc-groups');
		var columns = [];
		for (var i = 0; i < colCount; i++) {
			columns.push(el('div', 'zkc-col'));
			wrap.appendChild(columns[i]);
		}

		var ci = 0;
		var acc = 0;
		groups.forEach(function (grp, idx) {
			if (ci < colCount - 1 && acc + weights[idx] / 2 > target * (ci + 1)) {
				ci++;
			}
			acc += weights[idx];

			var block = el('div', 'zkc-group');
			block.appendChild(el('div', 'zkc-group-title', '<span>' + esc(grp.name) + '</span>'));
			var grid = el('div', 'zkc-tiles');
			grp.insurers.forEach(function (ins) {
				var tile = el('button', 'zkc-tile');
				tile.type = 'button';
				tile.title = ins.name;
				var logoHtml = ins.logo
					? '<img class="zkc-tile-logo" src="' + esc(ins.logo) + '" alt="' + esc(ins.name) + '" loading="lazy">'
					: '<span class="zkc-tile-initial">' + esc((ins.name || '?').slice(0, 3)) + '</span>';
				tile.innerHTML = '<span class="zkc-tile-media">' + logoHtml + '</span><span class="zkc-tile-name">' + esc(ins.name) + '</span>';
				tile.addEventListener('click', function () {
					self.state.insurer = ins;
					self.state.policy = null;
					self.goTo('policy');
				});
				grid.appendChild(tile);
			});
			block.appendChild(grid);
			columns[ci].appendChild(block);
		});
		card.appendChild(wrap);
		this.bindResize();
	};

	Calculator.prototype.columnCount = function () {
		var w = this.inner.offsetWidth || window.innerWidth || 1140;
		if (w < 560) return 1;
		if (w < 860) return 2;
		return 3;
	};

	Calculator.prototype.bindResize = function () {
		var self = this;
		if (this._resizeBound) return;
		this._resizeBound = true;
		window.addEventListener('resize', function () {
			clearTimeout(self._resizeTimer);
			self._resizeTimer = setTimeout(function () {
				if (self.state.screen === 'insurer' && self.columnCount() !== self._lastColCount) {
					self.render();
				}
			}, 150);
		});
	};

	/* Step 2 — policy */
	Calculator.prototype.renderPolicies = function (card) {
		var self = this;
		var s = this.cfg.steps.s2;
		var insurer = this.state.insurer || { name: '' };
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));
		card.appendChild(el('p', 'zkc-subtitle', esc(tpl(s.subtitle, { insurer: insurer.name }))));
		var help = this.helpBox(s.help);
		if (help) card.appendChild(help);

		var list = el('div', 'zkc-options');
		this.policiesFor(insurer.name).forEach(function (p) {
			var btn = el('button', 'zkc-option', esc(p.name));
			btn.type = 'button';
			btn.addEventListener('click', function () {
				self.state.policy = p;
				self.goTo('deductible');
			});
			list.appendChild(btn);
		});
		var unknown = el('button', 'zkc-option', esc(this.cfg.general.unknown));
		unknown.type = 'button';
		unknown.addEventListener('click', function () {
			self.state.policy = 'unknown';
			self.goTo('deductible');
		});
		list.appendChild(unknown);
		card.appendChild(list);

		this.navRow(card, {});
	};

	/* Step 3 — total deductible */
	Calculator.prototype.renderDeductible = function (card) {
		var self = this;
		var s = this.cfg.steps.s3;
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));
		if (s.text) card.appendChild(el('p', 'zkc-text', esc(s.text)));
		var help = this.helpBox(s.help);
		if (help) card.appendChild(help);

		var grid = el('div', 'zkc-amount-grid');
		(this.cfg.calc.deductibles || []).forEach(function (amount) {
			var btn = el('button', 'zkc-option zkc-option-amount', esc(fmt(amount)));
			btn.type = 'button';
			btn.addEventListener('click', function () {
				self.state.deductible = amount;
				self.goTo('used');
			});
			grid.appendChild(btn);
		});
		card.appendChild(grid);

		var unknown = el('button', 'zkc-option', esc(this.cfg.general.unknown));
		unknown.type = 'button';
		unknown.addEventListener('click', function () {
			self.state.deductible = 'unknown';
			self.goTo('used');
		});
		card.appendChild(unknown);

		this.navRow(card, {});
	};

	/* Step 4 — used deductible (validated) */
	Calculator.prototype.renderUsedDeductible = function (card) {
		var self = this;
		var s = this.cfg.steps.s4;
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));
		var help = this.helpBox(s.help);
		if (help) card.appendChild(help);

		card.appendChild(el('label', 'zkc-label', esc(s.fieldLabel)));

		var fieldWrap = el('div', 'zkc-input-wrap');
		fieldWrap.appendChild(el('span', 'zkc-input-prefix', '€'));
		var input = el('input', 'zkc-input');
		input.type = 'number';
		input.min = '0';
		input.inputMode = 'decimal';
		input.placeholder = s.placeholder || '';
		if (!this.state.usedUnknown && this.state.usedDeductible) {
			input.value = this.state.usedDeductible;
		}
		fieldWrap.appendChild(input);
		card.appendChild(fieldWrap);

		var error = el('p', 'zkc-error');
		error.hidden = true;
		card.appendChild(error);

		var checkWrap = el('label', 'zkc-check');
		var check = el('input', '');
		check.type = 'checkbox';
		check.checked = !!this.state.usedUnknown;
		checkWrap.appendChild(check);
		checkWrap.appendChild(el('span', '', esc(this.cfg.general.unknown)));
		card.appendChild(checkWrap);

		function clearError() {
			error.hidden = true;
			error.textContent = '';
		}

		check.addEventListener('change', function () {
			input.disabled = check.checked;
			if (check.checked) input.value = '';
			clearError();
		});
		input.addEventListener('input', clearError);
		input.disabled = check.checked;

		// The amount already used can never exceed the chosen total.
		var ceiling = (this.state.deductible === null || this.state.deductible === 'unknown')
			? Infinity
			: this.state.deductible;

		this.navRow(card, {
			next: function () {
				if (check.checked) {
					self.state.usedUnknown = true;
					self.state.usedDeductible = 0;
					return self.goNext();
				}
				var v = parseFloat(String(input.value).replace(',', '.'));
				if (isNaN(v) || v < 0) {
					error.textContent = s.errorInvalid;
					error.hidden = false;
					return;
				}
				if (v > ceiling) {
					error.textContent = s.errorMax;
					error.hidden = false;
					return;
				}
				self.state.usedUnknown = false;
				self.state.usedDeductible = Math.round(v);
				self.goNext();
			}
		});
	};

	/* Step 5 — how costs are determined */
	Calculator.prototype.renderInfo = function (card) {
		var self = this;
		var s = this.cfg.steps.s5;
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));
		card.appendChild(el('div', 'zkc-rich', s.content));
		if (s.panel) card.appendChild(el('div', 'zkc-panel zkc-rich', s.panel));
		this.navRow(card, { next: function () { self.goNext(); } });
	};

	/* Step 6 — reimbursement, led by the dark result hero */
	Calculator.prototype.renderReimbursement = function (card) {
		var self = this;
		var s = this.cfg.steps.s6;
		var r = this.reimbursement();
		var basis = this.basisInfo(r.basis);
		var insurer = this.state.insurer;

		var hero = el('div', 'zkc-hero');
		hero.appendChild(el('div', 'zkc-hero-kicker', esc(s.heroKicker)));
		hero.appendChild(el('h2', 'zkc-hero-title', esc(
			insurer ? tpl(s.heroTitle, { insurer: insurer.name }) : s.heroTitleFallback
		)));
		hero.appendChild(el('div', 'zkc-hero-pct', esc(r.label + '%')));
		hero.appendChild(el('div', 'zkc-hero-basis', esc(tpl(s.heroBasis, { basis: r.basisLabel }))));

		var estimate = el('div', 'zkc-hero-estimate');
		estimate.appendChild(el('div', 'zkc-hero-kicker', esc(s.estimateKicker)));
		estimate.appendChild(el('div', 'zkc-hero-amount', '± ' + esc(fmt(r.expected))));
		estimate.appendChild(el('div', 'zkc-hero-note', esc(tpl(s.estimateNote, {
			invoice: fmt(this.cfg.calc.avgInvoice)
		}))));
		hero.appendChild(estimate);

		if (r.note) hero.appendChild(el('p', 'zkc-hero-policy-note', esc(r.note)));
		card.appendChild(hero);

		var msg = tpl(r.unknown ? s.messageUnknown : s.message, {
			percentage: r.label,
			basis: r.basisLabel,
			note: r.note
		});
		card.appendChild(el('div', 'zkc-highlight', esc(msg)));

		var box = el('div', 'zkc-panel');
		box.appendChild(el('div', 'zkc-kicker', esc(s.basisKicker)));
		box.appendChild(el('h4', 'zkc-h4', esc(basis.title)));
		box.appendChild(el('p', 'zkc-text-sm', esc(basis.text)));
		card.appendChild(box);

		var det = el('details', 'zkc-accordion');
		det.appendChild(el('summary', '', esc(s.accordionLabel)));
		(this.cfg.bases || []).forEach(function (b) {
			det.appendChild(el('h4', 'zkc-h4', esc(b.title)));
			det.appendChild(el('p', 'zkc-text-sm', esc(b.text)));
		});
		card.appendChild(det);

		if (s.footnote) card.appendChild(el('p', 'zkc-muted', esc(s.footnote)));
		this.navRow(card, { next: function () { self.goNext(); } });
	};

	/* Step 7 — invoices */
	Calculator.prototype.renderInvoices = function (card) {
		var self = this;
		var s = this.cfg.steps.s7;
		var insurer = this.state.insurer || { name: '', agreement: false };
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));

		var yes = !!insurer.agreement;
		var badge = el('div', 'zkc-badge-box ' + (yes ? 'zkc-badge-ok' : 'zkc-badge-alert'));
		badge.appendChild(el('div', 'zkc-badge-kicker', esc(yes ? s.yesBadge : s.noBadge)));
		badge.appendChild(el('p', '', esc(tpl(yes ? s.yesIntro : s.noIntro, { insurer: insurer.name }))));
		card.appendChild(badge);

		card.appendChild(el('div', 'zkc-panel zkc-rich', yes ? s.yesContent : s.noContent));

		var note = yes ? s.yesNote : s.noNote;
		if (note) card.appendChild(el('div', 'zkc-note zkc-rich', note));

		this.navRow(card, { next: function () { self.goNext(); } });
	};

	/* Step 8 — authorization, only reached for insurers that may need one */
	Calculator.prototype.renderMachtiging = function (card) {
		var self = this;
		var m = this.cfg.machtiging;
		card.appendChild(el('h2', 'zkc-title', esc(m.title)));
		card.appendChild(el('div', 'zkc-rich', m.content));

		if (m.asks && m.asks.length) {
			var box = el('div', 'zkc-panel');
			box.appendChild(el('h4', 'zkc-h4', esc(m.asksTitle)));
			var ul = el('ul', 'zkc-list');
			m.asks.forEach(function (line) { ul.appendChild(el('li', '', esc(line))); });
			box.appendChild(ul);
			card.appendChild(box);
		}

		if (m.footnote) card.appendChild(el('p', 'zkc-muted', esc(m.footnote)));

		this.navRow(card, { next: function () { self.goNext(); } });
	};

	/* Step 9 — coulance */
	Calculator.prototype.renderCoulance = function (card) {
		var self = this;
		var s = this.cfg.steps.s8;
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));
		card.appendChild(el('div', 'zkc-rich', s.intro));

		if (s.conditions && s.conditions.length) {
			var box = el('div', 'zkc-panel');
			box.appendChild(el('h4', 'zkc-h4', esc(s.conditionsTitle)));
			var ul = el('ul', 'zkc-list');
			s.conditions.forEach(function (line) { ul.appendChild(el('li', '', esc(line))); });
			box.appendChild(ul);
			card.appendChild(box);
		}

		if (s.excluded && s.excluded.length) {
			var box2 = el('div', 'zkc-panel zkc-panel-muted');
			box2.appendChild(el('h4', 'zkc-h4', esc(s.excludedTitle)));
			var ul2 = el('ul', 'zkc-list');
			s.excluded.forEach(function (line) { ul2.appendChild(el('li', '', esc(line))); });
			box2.appendChild(ul2);
			card.appendChild(box2);
		}

		this.navRow(card, {
			next: function () { self.goNext(); },
			nextLabel: s.button
		});
	};

	/* Result */
	Calculator.prototype.renderResult = function (card) {
		var self = this;
		var r = this.cfg.result;
		var c = this.cfg.calc;
		var reimb = this.reimbursement();
		var own = this.ownCosts();

		var invoice = c.avgInvoice;
		var reimbursed = reimb.expected;
		var waived = Math.max(0, invoice - reimbursed);
		var pct = invoice > 0 ? Math.round((reimbursed / invoice) * 100) : 0;

		card.classList.add('zkc-card-result');
		var head = el('div', 'zkc-result-head');
		head.appendChild(el('div', 'zkc-kicker', esc(r.kicker)));
		head.appendChild(el('h2', 'zkc-title', esc(r.title)));
		card.appendChild(head);

		// Average invoice + split bar.
		var avgBox = el('div', 'zkc-panel zkc-avg');
		avgBox.appendChild(el('div', 'zkc-kicker', esc(r.avgLabel)));
		avgBox.appendChild(el('div', 'zkc-avg-amount', '± ' + esc(fmt(invoice))));
		avgBox.appendChild(el('p', 'zkc-text-sm zkc-center', esc(r.avgNote)));

		var bar = el('div', 'zkc-split');
		var left = el('div', 'zkc-split-left', esc(pct + '%'));
		left.style.width = pct + '%';
		var right = el('div', 'zkc-split-right', esc((100 - pct) + '%'));
		right.style.width = (100 - pct) + '%';
		bar.appendChild(left);
		bar.appendChild(right);
		avgBox.appendChild(bar);

		var map = { amount: fmt(reimbursed), percentage: reimb.label, basis: reimb.basisLabel };
		var legend = el('div', 'zkc-legend');
		var l1 = el('div', 'zkc-legend-item');
		l1.appendChild(el('h4', 'zkc-h4 zkc-dot zkc-dot-primary', esc(tpl(r.reimbursedLabel, map))));
		l1.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.reimbursedText, map))));
		var mapW = { amount: fmt(waived), percentage: reimb.label, basis: reimb.basisLabel };
		var l2 = el('div', 'zkc-legend-item');
		l2.appendChild(el('h4', 'zkc-h4 zkc-dot zkc-dot-dark', esc(tpl(r.waivedLabel, mapW))));
		l2.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.waivedText, mapW))));
		legend.appendChild(l1);
		legend.appendChild(l2);
		avgBox.appendChild(legend);
		card.appendChild(avgBox);

		// Own costs.
		var deductibleText = own.known
			? fmt(own.deductible)
			: fmt(own.deductibleMin) + ' – ' + fmt(own.deductibleMax);
		var totalText = own.known
			? fmt(own.total)
			: fmt(own.totalMin) + ' – ' + fmt(own.totalMax);

		var ownBox = el('div', 'zkc-own');
		ownBox.appendChild(el('div', 'zkc-kicker', esc(r.ownKicker)));
		ownBox.appendChild(el('h3', 'zkc-h3', esc(r.ownTitle)));

		var row1 = el('div', 'zkc-own-row');
		row1.appendChild(el('div', '', '<strong>' + esc(r.contributionLabel) + '</strong><span class="zkc-own-sub">' + esc(r.contributionText) + '</span>'));
		row1.appendChild(el('div', 'zkc-own-amount', esc(fmt(c.contribution))));
		ownBox.appendChild(row1);

		var row2 = el('div', 'zkc-own-row');
		row2.appendChild(el('div', '', '<strong>' + esc(r.deductibleLabel) + '</strong><span class="zkc-own-sub">' + esc(r.deductibleText) + '</span>'));
		row2.appendChild(el('div', 'zkc-own-amount', esc(deductibleText)));
		ownBox.appendChild(row2);

		var totalRow = el('div', 'zkc-own-row zkc-own-total');
		totalRow.appendChild(el('div', '', esc(r.totalLabel)));
		totalRow.appendChild(el('div', 'zkc-own-amount', esc(totalText)));
		ownBox.appendChild(totalRow);
		card.appendChild(ownBox);

		// Summary — a different sentence when the used deductible is unknown.
		var bold = function (t) { return '<strong>' + esc(t) + '</strong>'; };
		var summaryMap = {
			invoice: bold(fmt(invoice)),
			reimbursed: bold(fmt(reimbursed)),
			waived: bold(fmt(waived)),
			contribution: bold(fmt(c.contribution))
		};
		if (own.known) {
			summaryMap.deductible = bold(fmt(own.deductible));
			summaryMap.total = bold(fmt(own.total));
		} else {
			summaryMap.totalMin = bold(fmt(own.totalMin));
			summaryMap.totalMax = bold(fmt(own.totalMax));
		}
		var summary = tpl(esc(own.known ? r.summary : r.summaryUnknown), summaryMap);
		card.appendChild(el('div', 'zkc-summary', summary));

		// Accuracy warnings for anything the visitor could not answer.
		var warnings = [];
		if (reimb.unknown && r.notePolicyUnknown) warnings.push(r.notePolicyUnknown);
		if (!own.known && r.noteDeductibleUnknown) warnings.push(r.noteDeductibleUnknown);
		warnings.forEach(function (text) {
			card.appendChild(el('div', 'zkc-warning', esc(text)));
		});

		card.appendChild(el('p', 'zkc-muted zkc-disclaimer', esc(r.disclaimer)));

		// Buttons.
		var nav = el('div', 'zkc-nav zkc-nav-result');
		var restart = el('button', 'zkc-btn zkc-btn-ghost', esc(r.restart));
		restart.type = 'button';
		restart.addEventListener('click', function () { self.restart(); });
		nav.appendChild(restart);

		if (r.signupLabel) {
			var signup = el('a', 'zkc-btn zkc-btn-primary', esc(r.signupLabel));
			signup.href = r.signupUrl || '#';
			if (r.signupTarget) {
				signup.target = r.signupTarget;
				signup.rel = 'noopener';
			}
			nav.appendChild(signup);
		}
		card.appendChild(nav);

		var backWrap = el('div', 'zkc-result-back');
		var back = el('button', 'zkc-restart', esc(this.cfg.general.back));
		back.type = 'button';
		back.addEventListener('click', function () { self.goBack(); });
		backWrap.appendChild(back);
		card.appendChild(backWrap);
	};

	/* ---------------------------------------------------------------- boot */

	function init(scope) {
		(scope || document).querySelectorAll('.zkc[data-zkc-config]').forEach(function (node) {
			if (node.__zkc) return;
			node.__zkc = new Calculator(node);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(document); });
	} else {
		init(document);
	}

	// Elementor editor / preview support.
	window.addEventListener('elementor/frontend/init', function () {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/zkc_cost_calculator.default',
				function ($scope) {
					var elScope = $scope && $scope[0] ? $scope[0] : $scope;
					if (elScope && elScope.querySelectorAll) {
						elScope.querySelectorAll('.zkc[data-zkc-config]').forEach(function (node) {
							node.__zkc = new Calculator(node);
						});
					}
				}
			);
		}
	});
})();
