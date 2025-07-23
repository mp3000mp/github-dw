package main

import (
// 	"context"
	"fmt"
// 	"io"
// 	"log"
// 	"os"
	"time"

// 	"main/query"
// 	"main/system"

// 	"github.com/joho/godotenv"
)

func runPreroutine(c chan string) {
	time.Sleep(time.Second * 2)
	fmt.Println("inside pre routine")
	c <- "pre routine"
}
// func runRoutine1(c chan string) {
// 	c <- "routine 1"
// }
// func runRoutine2(c chan string) {
// 	c <- "routine 2"
// }

func main() {
  	tickReload := time.Second * 1
	ticker := time.NewTicker(tickReload)

	preroutineQueue := make(chan string)

// 	go func() {
// 		for {
// 			select {
// 			case aaa := <-preroutineQueue:
// 				fmt.Println(aaa)
// 				runPreroutine(preroutineQueue)
// 			}
// 		}
// 	}()

	go runPreroutine(preroutineQueue)

	for {
		select {
		case <-	ticker.C:
			fmt.Println("outside pre routine")
			go runPreroutine(preroutineQueue)
		}
	}
}
