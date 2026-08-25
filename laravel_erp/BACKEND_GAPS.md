# Admission backend gaps

The schema and platform foundation are broader than the currently published API. The following work is intentionally not represented as complete:

- Email verification/resend and password-reset transports are not yet published as API operations.
- Payment initiation records intent only. M-Pesa, card and bank provider adapters, signed webhook processing, reconciliation and authorised waiver endpoints are pending provider credentials and agreed callback contracts.
- Controlled document upload/download/scan endpoints are pending production object-storage and malware-scanner configuration.
- Administrative assignment, scoring, approval, decision-batch, offer, admission-roll and student-conversion endpoints are not yet published.
- Admission-letter PDF generation, QR token rotation/revocation and public minimal-data verification remain pending.
- Governed analytics APIs and background PDF/XLSX/CSV export workers remain pending; existing Blade analytics/reports are not the API contract.
- The OpenAPI document currently covers the delivered v1 slice and must grow with each published operation.

No missing endpoint silently simulates a production provider or grants a privileged action.
