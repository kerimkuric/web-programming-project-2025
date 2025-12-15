var UserService = {
    init: function () {
        var token = localStorage.getItem("user_token");
        if (token && token !== undefined) {
            // User already logged in, redirect if on login page
            if (window.location.pathname.includes('login.html')) {
                window.location.replace("index.html");
            }
        }

        // Set up login form validation if form exists
        if ($("#login-form").length) {
            $("#login-form").validate({
                submitHandler: function (form) {
                    var entity = Object.fromEntries(new FormData(form).entries());
                    UserService.login(entity);
                },
            });
        }

        // Set up register form validation if form exists
        if ($("#register-form").length) {
            $("#register-form").validate({
                submitHandler: function (form) {
                    var entity = Object.fromEntries(new FormData(form).entries());
                    UserService.register(entity);
                },
            });
        }
    },

    login: function (entity) {
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "auth/login",
            type: "POST",
            data: JSON.stringify(entity),
            contentType: "application/json",
            dataType: "json",
            success: function (result) {
                console.log(result);
                localStorage.setItem("user_token", result.data.token);
                localStorage.setItem("user_data", JSON.stringify(result.data));
                window.location.replace("index.html");
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                const errorMsg = XMLHttpRequest.responseJSON?.message || XMLHttpRequest.responseText || 'Error';
                toastr.error(errorMsg);
            },
        });
    },

    register: function (entity) {
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "auth/register",
            type: "POST",
            data: JSON.stringify(entity),
            contentType: "application/json",
            dataType: "json",
            success: function (result) {
                toastr.success("Registration successful! Please login.");
                setTimeout(function() {
                    window.location.replace("login.html");
                }, 1500);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                const errorMsg = XMLHttpRequest.responseJSON?.message || XMLHttpRequest.responseText || 'Error';
                toastr.error(errorMsg);
            },
        });
    },

    logout: function () {
        localStorage.clear();
        window.location.replace("login.html");
    },

    getCurrentUser: function() {
        const token = localStorage.getItem("user_token");
        if (!token) return null;
        
        const decoded = Utils.parseJwt(token);
        return decoded ? decoded.user : null;
    },

    isAdmin: function() {
        const user = this.getCurrentUser();
        return user && user.role === Constants.ADMIN_ROLE;
    },

    generateMenuItems: function(){
        const token = localStorage.getItem("user_token");
        if (!token) {
            window.location.replace("login.html");
            return;
        }

        const decoded = Utils.parseJwt(token);
        const user = decoded ? decoded.user : null;

        if (user && user.role){
            let nav = "";
            let main = "";
            switch(user.role) {
                case Constants.USER_ROLE:
                    nav = '<li class="nav-item"><a class="nav-link" href="#/dashboard">Dashboard</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/books">Books</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/authors">Authors</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/genres">Genres</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/borrowings">Borrowings</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/profile">Profile</a></li>'+
                        '<li class="nav-item"><button class="btn btn-primary" onclick="UserService.logout()">Logout</button></li>';
                    if ($("#tabs").length) $("#tabs").html(nav);
                    break;

                case Constants.ADMIN_ROLE:
                    nav = '<li class="nav-item"><a class="nav-link" href="#/dashboard">Dashboard</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/books">Books</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/authors">Authors</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/genres">Genres</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/users">Users</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/borrowings">Borrowings</a></li>'+
                        '<li class="nav-item"><a class="nav-link" href="#/profile">Profile</a></li>'+
                        '<li class="nav-item"><button class="btn btn-primary" onclick="UserService.logout()">Logout</button></li>';
                    if ($("#tabs").length) $("#tabs").html(nav);
                    break;

                default:
                    window.location.replace("login.html");
            }
        } else {
            window.location.replace("login.html");
        }
    }
};

