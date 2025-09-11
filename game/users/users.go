package users

import "fmt"

type User struct {
	name string
	id int
	money float64
}

func CreateUser(name string, id int) User {
	return User{name, id, 0}
}

func LoadUser(name string, id int) User {
	return CreateUser(name, id) // load from db
}

func SaveUser(user *User) {
	// persistence layer
}
 