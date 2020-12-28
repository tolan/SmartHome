import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, Router, NavigationEnd} from '@angular/router';
import {Location} from "@angular/common";
import {Title} from '@angular/platform-browser';

import {Subject} from 'rxjs';
import {debounceTime, filter} from 'rxjs/operators';

import {ContainerFactory, Container} from './lib/container';
import {UserService} from './services/user';
import {User} from './interfaces/user';

const DEFAULT_ROUTE = ['/devices'];

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.less']
})
export class AppComponent implements OnInit {

    public showLoader: boolean;
    private container: Container;
    private history: string[] = [];

    constructor(
        private userService: UserService,
        private location: Location,
        private router: Router,
        private containerFactory: ContainerFactory,
        private activatedRoute: ActivatedRoute,
        private titleService: Title
    ) {
        this.showLoader = false;
        this.container = this.containerFactory.getContainer('Loader');
    }

    ngOnInit() {
        const subject = new Subject();
        subject.pipe(
            debounceTime(200),
        ).subscribe((loading) => {
            this.showLoader = loading > 0;
        });
        this.container.get('loading').subscribe((loading) => {
            Promise.resolve(null).then(() => {
                if (loading > 0 && this.showLoader === false) {
                    this.showLoader = true;
                } else {
                    subject.next(loading);
                }
            });
        });

        this.userService.getUser().subscribe((user?: User) => {
            this.history = this.history.concat(this.location.path());

            if (!user) {
                this.router.navigate(['/login']);
            } else if (!user.permissions.length) {
                this.router.navigate(['/no-perms']);
            } else if (!this.location.path() || this.location.path() === '/login') {
                if (this.history.length > 1) {
                    const target = this.history.filter((path) => path !== '/login').filter(Boolean);
                    this.router.navigate(target.length ? [target[target.length - 1]] : DEFAULT_ROUTE);
                } else {
                    this.router.navigate(DEFAULT_ROUTE);
                }
            }
        });

        this.router.events.pipe(
            filter((event) => event instanceof NavigationEnd),
        ).subscribe((event: NavigationEnd) => {
            let child = this.activatedRoute.firstChild;
            const names: {[key: number]: string} = {};
            let i = 0;

            const next = (i: number) => (title: string) => {
                if (title) {
                    names[i] = title;
                }
                this.setTitle(names, i);
            }

            while (child) {
                if (child.snapshot.data && child.snapshot.data['title']) {
                    let title = child.snapshot.data['title'];
                    if (typeof title === "function") {
                        title = title(event, this.containerFactory, next(i));
                    } else if (title) {
                        names[i] = title;
                    }
                    i++;
                }

                if (child.firstChild) {
                    child = child.firstChild;
                } else {
                    break;
                }
            }

            this.setTitle(names, i);

        });
    }

    setTitle(names: {[key: number]: string}, i: number) {
        const titles: {[key: string]: string} = {};
        for (let k = 0; k <= i; k++) {
            if (names[k]) {
                titles[names[k]] = names[k];
            }
        }

        this.titleService.setTitle(Object.values(titles).length ? Object.values(titles).join(' - ') : 'Smart Home');
    }
}
