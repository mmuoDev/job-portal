Introduction: 
aDa is a job portal which connect company and expert freelancer/part-timer. It always keep track of freelancer performance, the more job freelancer completed the more benefit they
gained. There are 2 ranks for freelancer with 2 different proposal space as monthly limit for
submitting proposal. The ranks are: rank B with 20 pts and rank A with 40 pts.

Objective: 
This API was developed to:
● Enable company to create and post job as well as view proposal for their job postings
● Enable freelance to view jobs and submit proposal to it
● Employer can create job post, then they can either save it as draft or publish it
● Freelancer can view list of published jobs
● Freelancer can only submit one proposal to any published job
● Each application submitted by freelancer will reduce the proposal space by 2pts, so the
rank B freelancer can only submit 10times max and rank A can submit 20times max
● Employer can view proposal from freelancer for their job post

Documentation:

Base URL – localhost:8080
ApiKey is in the .env file

1. Create employer
/api/employer/register
Method – POST
Body Parameters – company_ame, email, password

ApiKey must be passed to the header (with ‘apiKey’ as the key) and content-type is json. 

2. Login for employer
/api/employer/login
Method – POST
Body Parameters – email, password

ApiKey must be passed to the header (with ‘apiKey’ as the key) and content-type is json. 

3. Create freelancer
/api/freelancer/register
Method – POST
Body Parameters – name, email, password, rank_id
NB: rank_id can either be 1 or 2. 

ApiKey must be passed to the header (with ‘apiKey’ as the key) and content-type is json. 

4. Login for freelancer
/api/freelancer/login
Method – POST
Body Parameters – email, password

ApiKey must be passed to the header (with ‘apiKey’ as the key) and content-type is json. 

5. Add a job posting
/api/jobs/create
Method – POST
Body parameters – title, description, status_id, created_by

NB: status_id can either be 1 or 2, that is, published or draft respectively. created_by is the id of the employer

Token must be passed to the header (with ‘token’ as the key) and content-type is json. 
6. View all published jobs
/api/jobs/published
Method – GET

Token must be passed to the header (with ‘token’ as the key) and content-type is json. 

7. Create a job proposal (for freelancer)
/api/jobs/proposal/create
Method – POST
Body parameters – proposal, job_id, freelancer_id

This endpoint ensures a freelancer does not exceed his monthly job proposals allocations.

Token must be passed to the header (with ‘token’ as the key) and content-type is json. 


8. Fetch all submitted proposals for a job

/proposals/{job_id}
Method – GET

NB: job_id is the id of the job posted.

Token must be passed to the header (with ‘token’ as the key) and content-type is json. 

Other notes:

This API was written using the Slim framework. API endpoints are grouped into services – Employer, Freelancer and Job, located in the src/services folder. 

The .env file contains the API key and database configurations. 
The database schema can be found in the project root folder – job_posting_schema.sql

To get started with testing this API, 
1. clone or download source code (unzip)
2. Run composer install
3. cd to project folder
4. Run this command “php -S localhost:8080 -t public public/index.php” to start the server.

See https://www.slimframework.com/

Contact Me: radioactive.uche11@gmail.com
