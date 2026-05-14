<!--
|--------------------------------------------------------------------------
| Script For updating the drug_form data
|--------------------------------------------------------------------------
| Created By : Santhosha G
| Created At : 07/05/2026
|
| Purpose :
| - Update drug_form as 'Tablet' for Lenalidomide and Pomalidomide
| - Update drug_form as 'Capsule' for all other drugs
|--------------------------------------------------------------------------
-->
UPDATE drugs
SET drug_form = CASE
    WHEN LOWER(drug_name) IN ('lenalidomide', 'pomalidomide')
        THEN 'Tablet'
    ELSE 'Capsule'
END;

<!--
|--------------------------------------------------------------------------
| Script For updating the policy assigned questions data
|--------------------------------------------------------------------------
| Created By : Santhosha G
| Created At : 12/05/2026
|
|--------------------------------------------------------------------------
-->
UPDATE policy_assigned_questions
SET question = TRIM(
    REGEXP_REPLACE(
        question,
        '[[:space:]]*[Pp][Aa][Tt][Hh][Ff][Ii][Nn][Dd][Ee][Rr][[:space:]]*',
        ' '
    )
)
WHERE LOWER(question) LIKE '%pathfinder%';

