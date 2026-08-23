# Actors and Roles

Roles bundle permissions; they do not define data scope. A role assignment is `(user, role, scope_type, scope_id, valid_from, valid_until)`. Every action must answer: **Who is allowed to perform this action, over which records, under which conditions?**

| Actor family        | Representative roles                                                    | Typical scope                       | Prohibited by default                               |
| ------------------- | ----------------------------------------------------------------------- | ----------------------------------- | --------------------------------------------------- |
| Public              | Visitor, prospective applicant, verifier                                | Public data                         | Internal or personal records                        |
| Applicant           | Applicant                                                               | Self                                | Other applications and admissions decisions         |
| Student             | Student, postgraduate, alumnus                                          | Self                                | Publishing results, changing ledgers, staff data    |
| Academic            | Lecturer, supervisor, advisor                                           | Assigned classes/advisees           | Unassigned students; result publication             |
| Academic leadership | HOD, Dean, Director                                                     | Department/faculty                  | Other units unless separately assigned              |
| Registry            | Admissions Officer, Records Officer, Registrar                          | Assigned campus/institution         | Financial approval and clinical records             |
| Examinations        | Marker, Moderator, Exam Officer, Senate Secretary                       | Assigned course/faculty/institution | Conflicting stages in one result workflow           |
| Finance             | Cashier, Accountant, Finance Officer, Bursar, Auditor                   | Cost centre/campus/institution      | Self-approval; source-record deletion               |
| HR/payroll          | HR Officer, Payroll Officer, HR Director                                | Institution                         | Clinic/counselling records; self-approval           |
| Operations          | Procurement Officer, Storekeeper, Asset Officer, Librarian, Warden, ICT | Functional unit                     | Approval stages conflicting with initiation         |
| Restricted care     | Clinician, Counsellor, authorized case worker                           | Assigned case/service               | Platform administrator access                       |
| Governance          | Committee Secretary, Senate/Council Member, University Secretary        | Committee/institution               | Draft decisions outside assignment                  |
| Executive           | VC, DVC, management viewer                                              | Institution, normally read-only     | Operational mutation unless explicitly granted      |
| Platform            | System Administrator, Security Officer, Integration Operator            | Institution/platform                | Business-data access solely because of admin status |
| External system     | M-Pesa, bank, Moodle, Koha, regulator, notification provider            | Contract-specific                   | Interactive user permissions                        |

## Segregation-of-duty constraints

- Marks entry, moderation, verification, approval, and publication must be independently assignable; incompatible stages cannot be held for the same transaction.
- Supplier creation, requisition, evaluation, purchase approval, receipt, invoice approval, and payment require configured conflict rules.
- Journal preparation and posting, payment initiation and approval, payroll preparation and authorization, and refund initiation and approval are separated.
- Role, permission, and scope administration requires privileged MFA and audit; users cannot approve their own elevation.
- Break-glass access is time-bound, reasoned, separately audited, alerted, and unavailable for restricted records unless the responsible data-owner policy explicitly permits it.
