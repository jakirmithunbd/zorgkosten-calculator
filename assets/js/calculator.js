/* Zorgkosten Calculator – frontend stepper
 * Renders an 8-step cost calculator from the JSON config produced by the
 * Elementor widget. No dependencies.
 */
(function () {
	'use strict';

	var TOTAL_STEPS = 8;

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
		this.state = {
			step: 0, // 0 = intro, 1..8 = steps, 9 = result
			insurer: null,
			policy: null,          // policy object or 'unknown'
			deductible: null,      // number or 'unknown'
			usedDeductible: 0,
			usedUnknown: false
		};
		this.render();
	}

	Calculator.prototype.basisInfo = function (key) {
		var bases = this.cfg.bases || [];
		for (var i = 0; i < bases.length; i++) {
			if (bases[i].key === key) return bases[i];
		}
		return { key: key, label: key, title: key, text: '' };
	};

	Calculator.prototype.currentPolicy = function () {
		var c = this.cfg.calc;
		if (this.state.policy && this.state.policy !== 'unknown') {
			return this.state.policy;
		}
		return {
			name: this.cfg.general.unknown,
			percentage: c.defaultPercentage,
			basis: c.defaultBasis,
			note: ''
		};
	};

	Calculator.prototype.policiesFor = function (insurerName) {
		return (this.cfg.policies || []).filter(function (p) {
			return p.insurer === insurerName;
		});
	};

	Calculator.prototype.setStep = function (n) {
		this.state.step = n;
		this.render();
		var rect = this.root.getBoundingClientRect();
		if (rect.top < 0) {
			this.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	};

	Calculator.prototype.restart = function () {
		this.state = { step: 0, insurer: null, policy: null, deductible: null, usedDeductible: 0, usedUnknown: false };
		this.render();
	};

	/* ---------------------------------------------------------------- render */

	Calculator.prototype.render = function () {
		var self = this;
		var g = this.cfg.general;
		this.inner.innerHTML = '';

		// Header.
		if (g.showHeader) {
			var header = el('div', 'zkc-header');
			header.appendChild(el('div', 'zkc-brand', esc(g.brand)));
			if (this.state.step > 0) {
				var restart = el('button', 'zkc-restart', esc(g.restart));
				restart.type = 'button';
				restart.addEventListener('click', function () { self.restart(); });
				header.appendChild(restart);
			}
			this.inner.appendChild(header);
		}

		// Progress (steps 1..8).
		if (this.state.step >= 1 && this.state.step <= TOTAL_STEPS) {
			var pct = Math.round(((this.state.step - 1) / (TOTAL_STEPS - 1)) * 100);
			var prog = el('div', 'zkc-progress');
			var meta = el('div', 'zkc-progress-meta');
			meta.appendChild(el('span', '', esc(tpl(g.stepCounter, { current: this.state.step, total: TOTAL_STEPS }))));
			meta.appendChild(el('span', '', pct + '%'));
			prog.appendChild(meta);
			var bar = el('div', 'zkc-progress-bar');
			var fill = el('div', 'zkc-progress-fill');
			fill.style.width = pct + '%';
			bar.appendChild(fill);
			prog.appendChild(bar);
			this.inner.appendChild(prog);
		}

		// Card.
		var card = el('div', 'zkc-card');
		this.inner.appendChild(card);

		switch (this.state.step) {
			case 0: this.renderIntro(card); break;
			case 1: this.renderInsurers(card); break;
			case 2: this.renderPolicies(card); break;
			case 3: this.renderDeductible(card); break;
			case 4: this.renderUsedDeductible(card); break;
			case 5: this.renderInfo(card); break;
			case 6: this.renderReimbursement(card); break;
			case 7: this.renderInvoices(card); break;
			case 8: this.renderCoulance(card); break;
			case 9: this.renderResult(card); break;
		}

		// Footer.
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
		back.addEventListener('click', function () { self.setStep(self.state.step - 1); });
		row.appendChild(back);
		if (opts.next) {
			var next = el('button', 'zkc-btn zkc-btn-primary', esc(opts.nextLabel || this.cfg.general.next));
			next.type = 'button';
			next.addEventListener('click', opts.next);
			row.appendChild(next);
		}
		card.appendChild(row);
	};

	/* Step 0 — intro */
	Calculator.prototype.renderIntro = function (card) {
		var self = this;
		var i = this.cfg.intro;
		var wrap = el('div', 'zkc-intro');
		wrap.appendChild(el('div', 'zkc-kicker', esc(i.kicker)));
		wrap.appendChild(el('h2', 'zkc-title zkc-intro-title', esc(i.title)));
		wrap.appendChild(el('p', 'zkc-intro-text', esc(i.text)));
		var btn = el('button', 'zkc-btn zkc-btn-primary', esc(i.button));
		btn.type = 'button';
		btn.addEventListener('click', function () { self.setStep(1); });
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
				var logoHtml = ins.logo
					? '<img class="zkc-tile-logo" src="' + esc(ins.logo) + '" alt="' + esc(ins.name) + '" loading="lazy">'
					: '<span class="zkc-tile-initial">' + esc((ins.name || '?').charAt(0)) + '</span>';
				tile.innerHTML = '<span class="zkc-tile-media">' + logoHtml + '</span><span class="zkc-tile-name">' + esc(ins.name) + '</span>';
				tile.addEventListener('click', function () {
					self.state.insurer = ins;
					self.state.policy = null;
					self.setStep(2);
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
				if (self.state.step === 1 && self.columnCount() !== self._lastColCount) {
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
				self.setStep(3);
			});
			list.appendChild(btn);
		});
		var unknown = el('button', 'zkc-option', esc(this.cfg.general.unknown));
		unknown.type = 'button';
		unknown.addEventListener('click', function () {
			self.state.policy = 'unknown';
			self.setStep(3);
		});
		list.appendChild(unknown);
		card.appendChild(list);

		this.navRow(card, {});
	};

	/* Step 3 — deductible */
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
				self.setStep(4);
			});
			grid.appendChild(btn);
		});
		card.appendChild(grid);

		var unknown = el('button', 'zkc-option', esc(this.cfg.general.unknown));
		unknown.type = 'button';
		unknown.addEventListener('click', function () {
			self.state.deductible = 'unknown';
			self.setStep(4);
		});
		card.appendChild(unknown);

		this.navRow(card, {});
	};

	/* Step 4 — used deductible */
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
		input.placeholder = s.placeholder || '';
		if (!this.state.usedUnknown && this.state.usedDeductible) {
			input.value = this.state.usedDeductible;
		}
		fieldWrap.appendChild(input);
		card.appendChild(fieldWrap);

		var checkWrap = el('label', 'zkc-check');
		var check = el('input', '');
		check.type = 'checkbox';
		check.checked = !!this.state.usedUnknown;
		checkWrap.appendChild(check);
		checkWrap.appendChild(el('span', '', esc(this.cfg.general.unknown)));
		card.appendChild(checkWrap);

		check.addEventListener('change', function () {
			input.disabled = check.checked;
		});
		input.disabled = check.checked;

		this.navRow(card, {
			next: function () {
				self.state.usedUnknown = check.checked;
				var v = parseFloat(input.value);
				self.state.usedDeductible = (check.checked || isNaN(v) || v < 0) ? 0 : v;
				self.setStep(5);
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
		this.navRow(card, { next: function () { self.setStep(6); } });
	};

	/* Step 6 — reimbursement */
	Calculator.prototype.renderReimbursement = function (card) {
		var self = this;
		var s = this.cfg.steps.s6;
		var pol = this.currentPolicy();
		var basis = this.basisInfo(pol.basis);
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));

		var msg = tpl(s.message, {
			percentage: Math.round(pol.percentage),
			basis: basis.label,
			note: pol.note || ''
		});
		if (pol.note && s.message.indexOf('{note}') === -1) {
			msg += ' (' + pol.note + ')';
		}
		card.appendChild(el('div', 'zkc-highlight', esc(msg)));

		var box = el('div', 'zkc-panel');
		box.appendChild(el('div', 'zkc-kicker', esc(s.basisKicker)));
		box.appendChild(el('h4', 'zkc-h4', esc(basis.title)));
		box.appendChild(el('p', 'zkc-text-sm', esc(basis.text)));
		card.appendChild(box);

		// Accordion with all bases.
		var det = el('details', 'zkc-accordion');
		det.appendChild(el('summary', '', esc(s.accordionLabel)));
		(this.cfg.bases || []).forEach(function (b) {
			det.appendChild(el('h4', 'zkc-h4', esc(b.title)));
			det.appendChild(el('p', 'zkc-text-sm', esc(b.text)));
		});
		card.appendChild(det);

		if (s.footnote) card.appendChild(el('p', 'zkc-muted', esc(s.footnote)));
		this.navRow(card, { next: function () { self.setStep(7); } });
	};

	/* Step 7 — invoices */
	Calculator.prototype.renderInvoices = function (card) {
		var self = this;
		var s = this.cfg.steps.s7;
		var insurer = this.state.insurer || { name: '', agreement: false, machtiging: false };
		card.appendChild(el('h2', 'zkc-title', esc(s.title)));

		var yes = !!insurer.agreement;
		var badge = el('div', 'zkc-badge-box' + (yes ? '' : ' zkc-badge-warn'));
		badge.appendChild(el('div', 'zkc-badge-kicker', esc(yes ? s.yesBadge : s.noBadge)));
		badge.appendChild(el('p', '', esc(tpl(yes ? s.yesIntro : s.noIntro, { insurer: insurer.name }))));
		card.appendChild(badge);

		var content = el('div', 'zkc-panel zkc-rich', yes ? s.yesContent : s.noContent);
		card.appendChild(content);

		var note = yes ? s.yesNote : s.noNote;
		if (note) card.appendChild(el('div', 'zkc-note zkc-rich', note));

		if (insurer.machtiging && s.machtigingContent) {
			var m = el('div', 'zkc-panel zkc-machtiging');
			m.appendChild(el('h3', 'zkc-h3', esc(s.machtigingTitle)));
			m.appendChild(el('div', 'zkc-rich', s.machtigingContent));
			card.appendChild(m);
		}

		this.navRow(card, { next: function () { self.setStep(8); } });
	};

	/* Step 8 — coulance */
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
			next: function () { self.setStep(9); },
			nextLabel: s.button
		});
	};

	/* Step 9 — result */
	Calculator.prototype.renderResult = function (card) {
		var self = this;
		var r = this.cfg.result;
		var c = this.cfg.calc;
		var pol = this.currentPolicy();
		var basis = this.basisInfo(pol.basis);

		var pct = Math.round(pol.percentage);
		var invoice = c.avgInvoice;
		var reimbursed = Math.round(invoice * (pct / 100));
		var waived = Math.round(invoice - reimbursed);
		var totalDeductible = (this.state.deductible === 'unknown' || this.state.deductible === null)
			? c.defaultDeductible
			: this.state.deductible;
		var remainingDeductible = Math.max(0, totalDeductible - (this.state.usedDeductible || 0));
		var total = c.contribution + remainingDeductible;

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

		var legend = el('div', 'zkc-legend');
		var l1 = el('div', 'zkc-legend-item');
		l1.appendChild(el('h4', 'zkc-h4 zkc-dot zkc-dot-primary', esc(tpl(r.reimbursedLabel, { amount: fmt(reimbursed), percentage: pct, basis: basis.label }))));
		l1.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.reimbursedText, { amount: fmt(reimbursed), percentage: pct, basis: basis.label }))));
		var l2 = el('div', 'zkc-legend-item');
		l2.appendChild(el('h4', 'zkc-h4 zkc-dot zkc-dot-dark', esc(tpl(r.waivedLabel, { amount: fmt(waived), percentage: 100 - pct, basis: basis.label }))));
		l2.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.waivedText, { amount: fmt(waived), percentage: 100 - pct, basis: basis.label }))));
		legend.appendChild(l1);
		legend.appendChild(l2);
		avgBox.appendChild(legend);
		card.appendChild(avgBox);

		// Own costs.
		var own = el('div', 'zkc-own');
		own.appendChild(el('div', 'zkc-kicker', esc(r.ownKicker)));
		own.appendChild(el('h3', 'zkc-h3', esc(r.ownTitle)));

		var row1 = el('div', 'zkc-own-row');
		row1.appendChild(el('div', '', '<strong>' + esc(r.contributionLabel) + '</strong><span class="zkc-own-sub">' + esc(r.contributionText) + '</span>'));
		row1.appendChild(el('div', 'zkc-own-amount', esc(fmt(c.contribution))));
		own.appendChild(row1);

		var row2 = el('div', 'zkc-own-row');
		row2.appendChild(el('div', '', '<strong>' + esc(r.deductibleLabel) + '</strong><span class="zkc-own-sub">' + esc(r.deductibleText) + '</span>'));
		row2.appendChild(el('div', 'zkc-own-amount', esc(fmt(remainingDeductible))));
		own.appendChild(row2);

		var totalRow = el('div', 'zkc-own-row zkc-own-total');
		totalRow.appendChild(el('div', '', esc(r.totalLabel)));
		totalRow.appendChild(el('div', 'zkc-own-amount', esc(fmt(total))));
		own.appendChild(totalRow);
		card.appendChild(own);

		// Summary.
		var bold = function (t) { return '<strong>' + esc(t) + '</strong>'; };
		var summary = tpl(esc(r.summary), {
			invoice: bold(fmt(invoice)),
			reimbursed: bold(fmt(reimbursed)),
			waived: bold(fmt(waived)),
			contribution: bold(fmt(c.contribution)),
			deductible: bold(fmt(remainingDeductible)),
			total: bold(fmt(total))
		});
		card.appendChild(el('div', 'zkc-summary', summary));
		card.appendChild(el('p', 'zkc-muted zkc-disclaimer', esc(r.disclaimer)));

		// Buttons.
		var nav = el('div', 'zkc-nav zkc-nav-result');
		var restart = el('button', 'zkc-btn zkc-btn-ghost', esc(r.restart));
		restart.type = 'button';
		restart.addEventListener('click', function () { self.restart(); });
		nav.appendChild(restart);

		var group = el('div', 'zkc-nav-group');
		var back = el('button', 'zkc-btn zkc-btn-ghost', esc(this.cfg.general.back));
		back.type = 'button';
		back.addEventListener('click', function () { self.setStep(8); });
		group.appendChild(back);

		if (r.signupLabel) {
			var signup = el('a', 'zkc-btn zkc-btn-primary', esc(r.signupLabel));
			signup.href = r.signupUrl || '#';
			if (r.signupTarget) {
				signup.target = r.signupTarget;
				signup.rel = 'noopener';
			}
			group.appendChild(signup);
		}
		nav.appendChild(group);
		card.appendChild(nav);
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
