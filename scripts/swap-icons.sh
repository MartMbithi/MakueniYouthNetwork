#!/usr/bin/env bash
# One-shot helper: convert Bootstrap Icons class refs (bi bi-X) to Remix
# Icon class refs (ri ri-Y) across the admin templates and JS.
# Idempotent: safe to re-run, no-ops if all "bi-" tokens are gone.

set -euo pipefail

cd "$(dirname "$0")/.."

FILES=(
  templates/layouts/admin.twig
  templates/admin/login.twig
  templates/admin/_partials/_form_helpers.twig
  templates/admin/dashboard.twig
  templates/admin/donations/index.twig
  templates/admin/events/form.twig
  templates/admin/events/index.twig
  templates/admin/messages/index.twig
  templates/admin/pages/form.twig
  templates/admin/pages/index.twig
  templates/admin/partners/form.twig
  templates/admin/partners/index.twig
  templates/admin/posts/form.twig
  templates/admin/posts/index.twig
  templates/admin/programs/form.twig
  templates/admin/programs/index.twig
  templates/admin/settings/index.twig
  templates/admin/stats/form.twig
  templates/admin/stats/index.twig
  templates/admin/users/form.twig
  templates/admin/users/index.twig
  templates/admin/volunteers/index.twig
  public/assets/js/admin.js
)

# Long-then-short ordering matters: "envelope-open" must run before "envelope".

sed -i '' \
  -e 's/bi bi-arrow-right/ri ri-arrow-right-line/g' \
  -e 's/bi bi-box-arrow-in-right/ri ri-login-box-line/g' \
  -e 's/bi bi-box-arrow-up-right/ri ri-external-link-line/g' \
  -e 's/bi bi-box-arrow-right/ri ri-logout-box-r-line/g' \
  -e 's/bi bi-calendar-event/ri ri-calendar-event-line/g' \
  -e 's/bi bi-cash-coin/ri ri-money-dollar-circle-line/g' \
  -e 's/bi bi-check-circle-fill/ri ri-checkbox-circle-fill/g' \
  -e 's/bi bi-check2-circle/ri ri-checkbox-circle-line/g' \
  -e 's/bi bi-cloud-arrow-up/ri ri-upload-cloud-2-line/g' \
  -e 's/bi bi-diagram-3/ri ri-organization-chart/g' \
  -e 's/bi bi-diagram-2/ri ri-node-tree/g' \
  -e 's/bi bi-download/ri ri-download-2-line/g' \
  -e 's/bi bi-envelope-open/ri ri-mail-open-line/g' \
  -e 's/bi bi-envelope/ri ri-mail-line/g' \
  -e 's/bi bi-exclamation-triangle-fill/ri ri-error-warning-fill/g' \
  -e 's/bi bi-eye/ri ri-eye-line/g' \
  -e 's/bi bi-file-earmark-text/ri ri-file-text-line/g' \
  -e 's/bi bi-file-earmark/ri ri-draft-line/g' \
  -e 's/bi bi-funnel/ri ri-filter-3-line/g' \
  -e 's/bi bi-globe2/ri ri-global-line/g' \
  -e 's/bi bi-graph-up/ri ri-bar-chart-line/g' \
  -e 's/bi bi-image/ri ri-image-line/g' \
  -e 's/bi bi-info-circle-fill/ri ri-information-fill/g' \
  -e 's/bi bi-link-45deg/ri ri-link/g' \
  -e 's/bi bi-list/ri ri-menu-line/g' \
  -e 's/bi bi-lock/ri ri-lock-line/g' \
  -e 's/bi bi-newspaper/ri ri-newspaper-line/g' \
  -e 's/bi bi-pencil-square/ri ri-edit-box-line/g' \
  -e 's/bi bi-pencil/ri ri-pencil-line/g' \
  -e 's/bi bi-people/ri ri-team-line/g' \
  -e 's/bi bi-person-badge/ri ri-id-card-line/g' \
  -e 's/bi bi-person-plus/ri ri-user-add-line/g' \
  -e 's/bi bi-plus-lg/ri ri-add-line/g' \
  -e 's/bi bi-shield-check/ri ri-shield-check-line/g' \
  -e 's/bi bi-shield-exclamation/ri ri-shield-cross-line/g' \
  -e 's/bi bi-shield-lock/ri ri-shield-keyhole-line/g' \
  -e 's/bi bi-sliders/ri ri-equalizer-line/g' \
  -e 's/bi bi-speedometer2/ri ri-dashboard-line/g' \
  -e 's/bi bi-telephone/ri ri-phone-line/g' \
  -e 's/bi bi-trash/ri ri-delete-bin-line/g' \
  -e 's/bi bi-upload/ri ri-upload-2-line/g' \
  "${FILES[@]}"

# Dynamic icon-class strings (Twig conditional templates).
# Match the value-only strings inside the {{ }} expressions and rewrite
# both prefix and name in one pass.

sed -i '' \
  -e "s/bi bi-{{ icon }}/ri ri-{{ icon }}/g" \
  -e "s/bi bi-{{ \(.*\)'envelope-open'/ri ri-{{ \1'mail-open-line'/g" \
  -e "s/bi bi-{{ \(.*\)'envelope'/ri ri-{{ \1'mail-line'/g" \
  -e "s/bi bi-{{ \(.*\)'exclamation-triangle-fill'/ri ri-{{ \1'error-warning-fill'/g" \
  -e "s/bi bi-{{ \(.*\)'check-circle-fill'/ri ri-{{ \1'checkbox-circle-fill'/g" \
  -e "s/bi bi-{{ \(.*\)'info-circle-fill'/ri ri-{{ \1'information-fill'/g" \
  -e "s/'envelope-open'/'mail-open-line'/g" \
  -e "s/'envelope'/'mail-line'/g" \
  -e "s/'exclamation-triangle-fill'/'error-warning-fill'/g" \
  -e "s/'check-circle-fill'/'checkbox-circle-fill'/g" \
  -e "s/'info-circle-fill'/'information-fill'/g" \
  "${FILES[@]}"

# Card-icon argument names passed to the h.card_open() macro.
# Format: h.card_open('Title', 'icon-name'[, 'extra-class']).

sed -i '' \
  -e "s/, 'pencil-square'/, 'edit-box-line'/g" \
  -e "s/, 'newspaper'/, 'newspaper-line'/g" \
  -e "s/, 'people'/, 'team-line'/g" \
  -e "s/, 'image'/, 'image-line'/g" \
  -e "s/, 'diagram-3'/, 'organization-chart'/g" \
  -e "s/, 'diagram-2'/, 'node-tree'/g" \
  -e "s/, 'calendar-event'/, 'calendar-event-line'/g" \
  -e "s/, 'file-earmark-text'/, 'file-text-line'/g" \
  -e "s/, 'graph-up'/, 'bar-chart-line'/g" \
  -e "s/, 'person-plus'/, 'user-add-line'/g" \
  -e "s/, 'shield-lock'/, 'shield-keyhole-line'/g" \
  -e "s/, 'shield-exclamation'/, 'shield-cross-line'/g" \
  -e "s/, 'palette'/, 'palette-line'/g" \
  -e "s/, 'building'/, 'building-line'/g" \
  -e "s/, 'telephone'/, 'phone-line'/g" \
  -e "s/, 'share'/, 'share-line'/g" \
  -e "s/, 'tag'/, 'price-tag-3-line'/g" \
  -e "s/, 'send-check'/, 'send-plane-line'/g" \
  -e "s/, 'person-badge'/, 'id-card-line'/g" \
  -e "s/, 'check2-circle'/, 'checkbox-circle-line'/g" \
  -e "s/, 'mail-line'/, 'mail-line'/g" \
  "${FILES[@]}"

echo "Done."
