Bravi Assessment Test
=====================

### Disclaimer
>*I've never used `docker` before, though I took the challenge — **at the expense of maybe failing the test** — just for the sake of learning. It looks like a ridiculously useful tool, and I'm certain I'll continue to make use of it in my future projects, regardless of whether I pass this test or not. Keep in mind that I'm aware that the way I set it up for this project may look a bit funky, but that's to be expected as I'm still in the beggining of docker's learning curve.*
>*Though, on the other hand, if anything regarding wordpress might look a bit funky, well... that's simply because it's wordpress.*

## Requirements

- `docker`

- `docker-compose`

## Instructions
1. Clone this repo and `cd` into the directory

2. Run the docker container:

        docker-compose up -d

3. Open your browser and point it to `localhost`

    - *PS: if you're already running anything on port 80, map a different port by editing the file `docker-compose.yml`.*

4. Profit.

## Here are some suggestions to get you started

- Log in as `admin:admin`
- Log in as `user:user`
- Try cheating!
    - Open the [search page](http://localhost/search/) — directly via the URL — while not logged in, see if it works<sup>[1](#footnote1)</sup>.


### Foot-Notes
<a name="footnote1">1</a>: If you open any "Private" page without the correct permissions, Wordpress' default behaviour is to 404 on you. The proper way to solve it and give you control over what'll be displayed — e.g.: an alternative login page — would be to create a custom page template that handles the verification of whether the user is logged in or not, and use it as the template of the page you're trying to restrict access. Setting the page as Private is simpler and works, but it's definitely a workaround, which could get messy in the long run, particularly if you have lots of pages to restrict access to.
