// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * M-SEB Minimum Time & Minimum Answered enforcement module.
 *
 * Prevents students from finishing a quiz attempt before meeting the
 * minimum time and minimum answered percentage requirements.
 *
 * @module   local_mseb/mintime
 * @package  local_mseb
 * @copyright 2024 M-SEB 
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string} from 'core/str';

/**
 * Initialise the mintime/minanswered enforcement.
 *
 * @param {number} timeCreated   Unix timestamp when the attempt started.
 * @param {number} minTimeMins   Minimum time in minutes (0 = disabled).
 * @param {number} minAnsweredPct  Minimum answered percentage (0 = disabled).
 * @param {number} serverTimeAtLoad Unix timestamp of server time when the page was rendered.
 */
export const init = (timeCreated, minTimeMins, minAnsweredPct, serverTimeAtLoad) => {
  const minTimeSecs = minTimeMins * 60;
  const localTimeAtLoad = Math.floor(Date.now() / 1000);

  /**
   * Calculate remaining seconds before the minimum time is met.
   *
   * @returns {number} Remaining seconds (0 if already met).
   */
  const getRemaining = () => {
    if (minTimeSecs <= 0) {
      return 0;
    }
    const currentLocalTime = Math.floor(Date.now() / 1000);
    const elapsedSinceLoad = currentLocalTime - localTimeAtLoad;
    const approxServerTime = serverTimeAtLoad + elapsedSinceLoad;
    const elapsed = approxServerTime - timeCreated;
    return minTimeSecs - elapsed;
  };

  /**
   * Calculate the percentage of answered questions.
   *
   * @returns {number} Percentage (0–100).
   */
  const getAnsweredPercent = () => {
    // Try the navigation buttons (works on attempt and summary pages).
    const qnButtons = document.querySelectorAll('.qnbutton, a.qnbutton, div.qnbutton');
    if (qnButtons.length > 0) {
      let answered = 0;
      qnButtons.forEach(btn => {
        if (
          btn.classList.contains('answersaved') ||
          btn.classList.contains('requiresgrading') ||
          btn.classList.contains('answered')
        ) {
          answered++;
        }
      });
      return (answered / qnButtons.length) * 100;
    }

    // Fallback to the summary table.
    const summaryRows = document.querySelectorAll('table.quizsummaryofattempt tbody tr');
    if (summaryRows.length > 0) {
      let answered = 0;
      summaryRows.forEach(row => {
        const text = (row.textContent || '').toLowerCase();
        if (
          text.includes('saved') ||
          text.includes('answered') ||
          text.includes('tersimpan') ||
          text.includes('disimpan') ||
          text.includes('diisi') ||
          text.includes('menjawab') ||
          text.includes('grading')
        ) {
          answered++;
        }
      });
      return (answered / summaryRows.length) * 100;
    }

    // Fallback: if questions are visible but we can't determine count, be safe.
    const questions = document.querySelectorAll('.que');
    if (questions.length > 0) {
      return 0;
    }

    return 100;
  };

  // Inject sledgehammer CSS immediately to hide finish buttons.
  const msebStyle = document.createElement('style');
  msebStyle.id = 'mseb-sledgehammer';
  msebStyle.textContent = `
    form:not(#responseform)[action*="processattempt.php"] {
      display: none !important; opacity: 0 !important; visibility: hidden !important;
      pointer-events: none !important; margin: 0 !important; padding: 0 !important;
      border: 0 !important; width: 0 !important; height: 0 !important;
      overflow: hidden !important; position: absolute !important; left: -9999px !important;
    }
    a[href*="summary.php"] { display: none !important; pointer-events: none !important; visibility: hidden !important; }
    .endtestlink { display: none !important; pointer-events: none !important; visibility: hidden !important; }
    input[name="next"][value*="Selesai"], input[name="next"][value*="selesai"],
    input[name="next"][value*="Finish"], input[name="next"][value*="finish"] {
      display: none !important; pointer-events: none !important; visibility: hidden !important;
    }
  `;

  if (minTimeSecs > 0 || minAnsweredPct > 0) {
    document.head.appendChild(msebStyle);
  }

  // Block form submission if requirements not met.
  window.addEventListener('submit', async (e) => {
    const remaining = getRemaining();
    const answeredPercent = getAnsweredPercent();

    if (remaining > 0 || answeredPercent < minAnsweredPct) {
      const f = e.target;
      let isFinishAttempt = false;

      if (f.action && f.action.includes('processattempt.php')) {
        if (f.id !== 'responseform') {
          isFinishAttempt = true;
        }
        if (
          e.submitter &&
          (e.submitter.name.includes('finish') ||
            (e.submitter.value && e.submitter.value.toLowerCase().includes('selesai')))
        ) {
          isFinishAttempt = true;
        }
        if (f.querySelector('input[name="finishattempt"]')) {
          isFinishAttempt = true;
        }
      }

      if (isFinishAttempt) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
      }
    }
  }, true);

  // Block click on summary/endtest links.
  document.addEventListener('click', (e) => {
    const remaining = getRemaining();
    const answeredPercent = getAnsweredPercent();

    if (remaining > 0 || answeredPercent < minAnsweredPct) {
      const t = e.target;
      if (t.closest('a[href*="summary.php"]') || t.closest('.endtestlink')) {
        e.preventDefault();
        e.stopPropagation();
      }
    }
  }, true);

  // Periodic tick to show/hide the sledgehammer CSS.
  document.addEventListener('DOMContentLoaded', () => {
    const tick = () => {
      const remaining = getRemaining();
      const answeredPercent = getAnsweredPercent();
      const timeMet = remaining <= 0;
      const answeredMet = answeredPercent >= minAnsweredPct;

      if (!timeMet || !answeredMet) {
        if (!document.getElementById('mseb-sledgehammer') && (minTimeSecs > 0 || minAnsweredPct > 0)) {
          document.head.appendChild(msebStyle);
        }
      } else {
        const lock = document.getElementById('mseb-sledgehammer');
        if (lock) {
          lock.remove();
        }
      }
    };

    setInterval(tick, 1000);
    tick();
  });
};
